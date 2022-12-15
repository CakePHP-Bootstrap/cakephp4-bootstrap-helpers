<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mikaël Capelle (https://typename.fr)
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 * @link        https://holt59.github.io/cakephp3-bootstrap-helpers/
 */
namespace Bootstrap\View\Helper;

/**
 * BreadcrumbsHelper to register and display a breadcrumb trail for your views.
 *
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class BreadcrumbsHelper extends \Cake\View\Helper\BreadcrumbsHelper
{
    /**
     * Default config for the helper.
     *
     * @var array
     * @link https://api.cakephp.org/3.3/class-Cake.View.Helper.BreadcrumbsHelper.html
     */
    protected $_defaultConfig = [
        // phpcs:disable Generic.Files.LineLength.TooLong
        'templates' => [
            'wrapper' => '<ol class="breadcrumb{{attrs.class}}"{{attrs}}>{{content}}</ol>',
            'item' => '<li class="breadcrumb-item{{attrs.class}}"{{attrs}}><a href="{{url}}"{{innerAttrs}}>{{title}}</a></li>',
            'itemWithoutLink' => '<li class="breadcrumb-item active{{attrs.class}}" aria-current="page"{{attrs}}>{{title}}</li>',
            'separator' => '',
        ],
        // phpcs:enable
        'templateClass' => 'Bootstrap\View\EnhancedStringTemplate',
    ];
}
