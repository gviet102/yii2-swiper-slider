<?php
/**
 * Created on Tue Oct 27 2020.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @copyright Copyright (c) 2010 - 2020 Sergey Coderius
 * @author Sergey Coderius <sunrise4fun@gmail.com>
 *
 * @see https://github.com/coderius - My github. See more my packages here...
 * @see https://coderius.biz.ua/ - My dev. blog
 *
 * Contact email: sunrise4fun@gmail.com - Have suggestions, contact me |:=)
 */

namespace bean\swiperslider;

use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

class SwiperSlider extends Widget
{
    const EVENT_BEFORE_REGISTER_DEFAULT_ASSET = 'beforeRegisterDefaultAsset';
    const EVENT_AFTER_REGISTER_DEFAULT_ASSET = 'afterRegisterDefaultAsset';

    const WIDGET_NAME = 'swiper';
    const JS_PLUGIN_NAME = 'Swiper';

    const CONTAINER = 'container';
    const WRAPPER = 'wrapper';
    const SLIDE = 'slide';
    const PAGINATION = 'pagination';
    const BUTTON_PREV = 'button-prev';
    const BUTTON_NEXT = 'button-next';
    const SCROLLBAR = 'scrollbar';

    const ASSET_DEFAULT = 'coderius\swiperslider\SwiperSliderAsset';

    /**
     * Cdn base url.
     *
     * @var string
     */
    const CDN_BASE_URL = 'https://unpkg.com/swiper';

    /**
     * Generate css class name for item.
     *
     * @param string $itemName
     * @param bool   $prefix
     *
     * @return string
     */
    public static function getItemCssClass($itemName, $prefix = true)
    {
        $prefix = $prefix ? '.' : '';

        return $prefix.self::WIDGET_NAME.'-'.$itemName;
    }

    /**
     * Widget options like inline styles etc.
     *
     * @var array
     */
    public $options = [];

    /**
     * If we need pagination.
     *
     * @var boolean
     */
    public $showPagination = true;

    /**
     * If we need scrollbar.
     *
     * @var boolean
     */
    public $showScrollbar = false;

    /**
     * Options in js plugin instance.
     *
     * @var array
     */
    public $clientOptions = [];

    /**
     * Default options for js plugin.
     *
     * @var array
     */
    public $defaultClientOptions = [];

    /**
     * If is allowed cdn base url to assets.
     *
     * @var boolean
     */
    public $assetFromCdn = false;

    /**
     * Sliders.
     *
     * @var array
     */
    public $slides = [];

    /**
     * Uniq widget name.
     *
     * @var string
     */
    protected $widgetId;

    protected $slideClass = "coderius\swiperslider\SlideDefault";

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->defaultClientOptions = [
            'loop' => true,
            'pagination' => ['el' => static::getItemCssClass(static::PAGINATION)],
            'navigation' => [
                    'nextEl' => static::getItemCssClass(static::BUTTON_NEXT),
                    'prevEl' => static::getItemCssClass(static::BUTTON_PREV),
            ],
        ];

        $this->widgetId = $this->getId().'-'.static::WIDGET_NAME;

        if ($this->slides === null || empty($this->slides)) {
            throw new InvalidConfigException("The 'slides' option is required");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->registerAssets();
        $this->registerPluginJs();
        echo $this->makeHtml();
    }

    /**
     * Processed registration all needed assets to widget
     * We can register custom asset by CustomAsset::register($view) by event hendler in widget options
     * echo SwiperSlider::widget([
     *      'on ' . SwiperSlider::EVENT_AFTER_REGISTER_DEFAULT_ASSET => function(){
     *                  CustomAsset::register($view)
     *       },
     *  ...
     *  ]);.
     *
     * @return void
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        $this->trigger(self::EVENT_BEFORE_REGISTER_DEFAULT_ASSET);
        $dafaultAsset = static::ASSET_DEFAULT;
        $bundle = $dafaultAsset::register($view);
        false === $this->assetFromCdn ?: $bundle->fromCdn(static::CDN_BASE_URL);
        $this->trigger(self::EVENT_AFTER_REGISTER_DEFAULT_ASSET);
    }

    /**
     * Create html elements for widget.
     *
     * @return void
     */
    protected function makeHtml()
    {
        //Slides
        //S
        $slides = [];
        $index = 0;
        foreach ($this->slides as $slide) {
            if (is_string($slide)) {
                $htmlSlide = $this->getHtmlElem(static::SLIDE, [], $slide);
            } else {
                //Mergin current slide attributes with global widget options styles pasted to all elements on this type
                //Example in widget init options -  `SwiperSlider::SLIDE => ["text-align" => "center"]`
                $slide['options'] = $this->mergeGlobalStyles(static::SLIDE, $slide['options']);
                $inctanseSlide = \Yii::createObject(array_merge([
                    'class' => $this->slideClass ?: SlideDefault::class,
                    'slider' => $this,
                ], $slide));
                //Invoke function in instance SlideDefault::renderSlideHtml
                $htmlSlide = $inctanseSlide->renderSlideHtml('div', $index);
            }

            $slides[] = $htmlSlide;
            ++$index;
        }
        $slides = "\n".implode("\n", $slides)."\n";

        //Slides wrapper
        $wrapper = $this->getHtmlElem(static::WRAPPER, [], $slides);

        //Pagination
        $pagination = $this->getHtmlElem(static::PAGINATION);

        //Navigation buttons
        $buttonPrev = $this->getHtmlElem(static::BUTTON_PREV);
        $buttonNext = $this->getHtmlElem(static::BUTTON_NEXT);

        //Scrollbar
        $scrollbar = $this->getHtmlElem(static::SCROLLBAR);

        //Collect all content
        $content = [];
        $content[] = $wrapper;

        // And if we need pagination
        if ($this->showPagination) {
            $content[] = $pagination;
        }

        $content[] = $buttonPrev;
        $content[] = $buttonNext;

        // And if we need scrollbar
        if ($this->showScrollbar) {
            $content[] = $scrollbar;
        }

        $content = "\n".implode("\n", $content)."\n";

        //Common container
        $container = "\n";
        $container .= "<!-- ***Swiper slider widget id: {$this->widgetId}*** -->";
        $container .= "\n";
        $container .= $this->getHtmlElem(static::CONTAINER, ['id' => $this->widgetId], $content);
        $container .= "\n<!-- ///Swiper slider widget id: {$this->widgetId}/// -->";

        return  $container;
    }

    /**
     * getHtmlElem function help create html element and add custom inline css styles.
     *
     * @param string $itemName
     * @param array  $options
     * @param string $content
     * @param string $tag
     *
     * @return string
     */
    protected function getHtmlElem($itemName, $options = [], $content = '', $tag = 'div')
    {
        $options = $this->mergeGlobalStyles($itemName, $options);

        return Html::tag($tag, $content, $options);
    }

    /**
     * Merge options array with default params like `class` and global options pasted when widget created
     * Example:
     * echo SwiperSlider::widget([
     * ...
     * 'options' => [
     *      'styles' => [
     *          SwiperSlider::CONTAINER => ["height" => "100px"],
     *          SwiperSlider::SLIDE => ["text-align" => "center"],
     *      ],
     *      'show-scrollbar' => true,
     *  ],
     * ...
     * ]);.
     *
     * In this example we merge options for html elements `container`  and  `slide` and default created options `class` for them getted
     * by function static::getItemCssClass($itemName, false)
     *
     * @param string $itemName
     * @param [type] $options
     *
     * @return void
     */
    protected function mergeGlobalStyles($itemName, $options)
    {
        $options = ArrayHelper::merge(['class' => static::getItemCssClass($itemName, false)], $options);
        $style = !empty($this->options['styles'][$itemName]) ? $this->options['styles'][$itemName] : null;
        Html::addCssStyle($options, $style);

        return $options;
    }

    /**
     * registerPluginJs function.
     *
     * @return void
     */
    protected function registerPluginJs()
    {
        $view = $this->getView();
        $pluginParams = [];
        $pluginParams[] = JsHelper::addString('#'.$this->widgetId);
        $clientOptions = ArrayHelper::merge($this->defaultClientOptions, $this->clientOptions);
        $pluginParams[] = Json::encode($clientOptions);
        $pluginInstance = JsHelper::newJsObject(static::JS_PLUGIN_NAME, $pluginParams);
        $jsVar = JsHelper::initVar('mySwiper', $pluginInstance);

        $view->registerJs($jsVar, \yii\web\View::POS_END);
    }
}
