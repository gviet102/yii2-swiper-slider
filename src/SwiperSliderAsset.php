<?php

/**
 * @license BSD-3-Clause
 * @copyright Copyright (C) 2012-2020 Sergio coderius <coderius>
 * @contacts sunrise4fun@gmail.com - Have suggestions, contact me :)
 *
 * @see https://github.com/coderius - My github
 */

namespace coderius\swiperslider;

use Yii;
use yii\web\AssetBundle;

/**
 * Asset bundle SwiperSlider.
 */
class SwiperSliderAsset extends AssetBundle
{
    public $sourcePath = '@npm/swiper';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setupAssets('css', ['swiper-bundle']);
        $this->setupAssets('js', ['swiper-bundle']);
        parent::init();
    }

    /**
     * Create cdn url like base path to assets.
     *
     * @param string $cdn
     *
     * @return void
     */
    public function fromCdn($cdn)
    {
        $this->sourcePath = null;
        $this->baseUrl = $cdn;
    }

    /**
     * Set js or css props to AssetBundle.
     *
     * @see yii\web\AssetBundle
     *
     * @param string $ext
     * @param array  $paths
     *
     * @return void
     */
    public function setupAssets($ext, $paths)
    {
        $allowedExts = ['css', 'js'];

        if (!in_array($ext, $allowedExts)) {
            throw new \InvalidArgumentException("Extention {$ext} not allowed");
        }

        $fullFiles = [];
        $minFiles = [];

        $fullFiles[] = static::makePathAssets($ext, $paths);
        $minFiles[] = static::makePathAssets($ext, $paths, 'min');

        $this->$ext = YII_DEBUG ? $fullFiles : $minFiles;
    }

    /**
     * Make path for asset.
     *
     * @param string      $ext
     * @param array       $paths
     * @param bool|string $pref
     *
     * @return array
     */
    public static function makePathAssets($ext, $paths, $pref = false)
    {
        $p = [];
        foreach ($paths as $path) {
            $p[] = $pref ? "{$path}.{$pref}.{$ext}" : "{$path}.{$ext}";
        }

        return $p;
    }

    /**
     * Registers this asset bundle with a view.
     *
     * @param View $view the view to be registered with
     *
     * @return static the registered asset bundle instance
     */
    public static function register($view, $cdn = false)
    {
        $bundle = parent::register($view);

        if ($cdn) {
            $bundle->fromCdn($cdn);
        }

        return $bundle;
    }
}
