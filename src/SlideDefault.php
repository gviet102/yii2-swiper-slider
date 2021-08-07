<?php
/**
 * Created on Wed Oct 28 2020.
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

namespace coderius\swiperslider;

use Closure;
use yii\base\BaseObject;
use yii\helpers\Html;

 class SlideDefault extends BaseObject
 {
     /**
      * @var SwiperSlider widget object that owns this slide.
      */
     public $slider;
     /**
      * @var array the HTML attributes for the slide tag.
      *
      * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
      */
     public $options = [];
     /**
      * Vilue contain all content pasted from tag.
      *
      * @var string
      */
     public $value = '';

     /**
      * Undocumented function.
      *
      * @param string $tag
      * @param int    $index $index the zero-based index of the data item among the item array returned by [[SwiperSlider::makeHtml]]
      *
      * @return string
      */
     public function renderSlideHtml($tag, $index)
     {
         $content = $this->value;
         if ($this->value instanceof Closure) {
             $content = call_user_func($this->value, $tag, $index, $this);
         } else {
             $content = $this->value;
         }

         $options = $this->options;

         return Html::tag($tag, $content, $options);
     }
 }
