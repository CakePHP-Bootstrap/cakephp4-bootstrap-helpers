<?php
declare(strict_types=1);

/**
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE file
 * Redistributions of files must retain the above copyright notice.
 * You may obtain a copy of the License at
 *
 *     https://opensource.org/licenses/mit-license.php
 *
 *
 * @copyright Copyright (c) Mikaël Capelle (https://typename.fr)
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Bootstrap\View\Helper;

use Cake\Utility\Hash;
use Cake\View\View;

/**
 * BreadcrumbsHelper to register and display a breadcrumb trail for your views.
 *
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class BreadcrumbsHelper extends \Cake\View\Helper\BreadcrumbsHelper
{

    /**
     * Default configuration for this helper.
     * Don't override parent::$_defaultConfig for robustness
     *
     * @var array
     */
    protected $helperConfig = [
        'templates' => [
            'wrapper' => '<ol class="breadcrumb{{attrs.class}}"{{attrs}}>{{content}}</ol>',
            'item' => '<li{{attrs}}><a href="{{url}}"{{innerAttrs}}>{{title}}</a></li>',
            'itemWithoutLink' => '<li class="active{{attrs.class}}"{{attrs}}>{{title}}</li>',
            'separator' => '',
        ],
        'templateClass' => 'Bootstrap\View\EnhancedStringTemplate',
    ];

    /**
     * @inheritDoc
     */
    public function __construct(View $View, array $config = [])
    {
        // Default config. Use Hash::merge() to keep default values
        $this->_defaultConfig = Hash::merge($this->_defaultConfig, $this->helperConfig);

        parent::__construct($View, $config);
    }
}
