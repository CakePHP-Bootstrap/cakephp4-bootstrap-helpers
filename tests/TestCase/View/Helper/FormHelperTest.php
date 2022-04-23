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

namespace Bootstrap\Test\TestCase\View\Helper;

use Bootstrap\View\Helper\FormHelper;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Cake\View\View;

class FormHelperTest extends TestCase
{
    /**
     * @var \Cake\View\View
     */
    protected $View;

    /**
     * Instance of FormHelper.
     *
     * @var FormHelper
     */
    public $form;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $request = new ServerRequest([
            'url' => '/',
            'params' => [
                'plugin' => null,
                'controller' => '',
                'action' => 'index',
            ],
        ]);

        $this->View = new View($request);
        $this->View->loadHelper('Html', [
            'className' => 'Bootstrap.Html',
        ]);
        $this->form = new FormHelper($this->View);
        $this->dateRegex = [
            'daysRegex' => 'preg:/(?:<option value="0?([\d]+)">\\1<\/option>[\r\n]*)*/',
            'monthsRegex' => 'preg:/(?:<option value="[\d]+">[\w]+<\/option>[\r\n]*)*/',
            'yearsRegex' => 'preg:/(?:<option value="([\d]+)">\\1<\/option>[\r\n]*)*/',
            'hoursRegex' => 'preg:/(?:<option value="0?([\d]+)">\\1<\/option>[\r\n]*)*/',
            'minutesRegex' => 'preg:/(?:<option value="([\d]+)">0?\\1<\/option>[\r\n]*)*/',
            'meridianRegex' => 'preg:/(?:<option value="(am|pm)">\\1<\/option>[\r\n]*)*/',
        ];

        // from CakePHP FormHelperTest
        $this->article = [
            'schema' => [
                'id' => ['type' => 'integer'],
                'author_id' => ['type' => 'integer', 'null' => true],
                'title' => ['type' => 'string', 'null' => true],
                'body' => 'text',
                'published' => ['type' => 'string', 'length' => 1, 'default' => 'N'],
                '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
            ],
            'required' => [
                'author_id' => true,
                'title' => true,
            ],
        ];

        Configure::write('debug', true);
    }

    public function testCreate()
    {
        // Standard form
        $this->assertHtml([
            ['form' => [
                'method',
                'accept-charset',
                'role' => 'form',
                'action'
            ]]
        ], $this->form->create());
        // Horizontal form
        $result = $this->form->create(null, ['horizontal' => true]);
        $this->assertEquals($this->form->horizontal, true);
        // Automatically return to non horizonal form
        $result = $this->form->create();
        $this->assertEquals($this->form->horizontal, false);
        // Inline form
        $result = $this->form->create(null, ['inline' => true]);
        $this->assertEquals($this->form->inline, true);
        $this->assertHtml([
            ['form' => [
                'method',
                'accept-charset',
                'role' => 'form',
                'action',
                'class' => 'form-inline'
            ]]
        ], $result);
        // Automatically return to non horizonal form
        $result = $this->form->create();
        $this->assertEquals($this->form->inline, false);
    }

    public function testColumnSizes()
    {
        $this->form->setConfig('columns', [
            'md' => [
                'label' => 2,
                'input' => 6,
                'error' => 4
            ],
            'sm' => [
                'label' => 12,
                'input' => 12,
                'error' => 12
            ]
        ], false);
        $this->form->create(null, ['horizontal' => true]);
        $result = $this->form->control('test', ['type' => 'text']);
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['label' => [
                'class' => 'control-label col-md-2 col-sm-12',
                'for' => 'test'
            ]],
            'Test',
            '/label',
            ['div' => [
                'class' => 'col-md-6 col-sm-12'
            ]],
            ['input' => [
                'type'  => 'text',
                'class' => 'form-control',
                'name'  => 'test',
                'id'    => 'test'
            ]],
            '/div',
            '/div'
        ];
        $this->assertHtml($expected, $result);

        $this->article['errors'] = [
            'Article' => [
                'title' => 'error message',
                'content' => 'some <strong>test</strong> data with <a href="#">HTML</a> chars'
            ]
        ];

        $this->form->setConfig('columns', [
            'md' => [
                'label' => 2,
                'input' => 6,
                'error' => 4
            ],
            'sm' => [
                'label' => 4,
                'input' => 8,
                'error' => 0
            ]
        ], false);
        $this->form->create($this->article, ['horizontal' => true]);
        $result = $this->form->control('Article.title', ['type' => 'text']);
        $expected = [
            ['div' => [
                'class' => 'form-group has-error text'
            ]],
            ['label' => [
                'class' => 'control-label col-md-2 col-sm-4',
                'for' => 'article-title'
            ]],
            'Title',
            '/label',
            ['div' => [
                'class' => 'col-md-6 col-sm-8'
            ]],
            ['input' => [
                'type'         => 'text',
                'class'        => 'form-control has-error',
                'name'         => 'Article[title]',
                'id'           => 'article-title',
                'aria-invalid' => 'true'
            ]],
            '/div',
            ['span' => [
                'class' => 'help-block error-message col-md-offset-0 col-md-4 col-sm-offset-4 col-sm-8'
            ]],
            'error message',
            '/span',
            '/div'
        ];
        $this->assertHtml($expected, $result);
    }

    public function testButton()
    {
        // default button
        $button = $this->form->button('Test');
        $this->assertHtml([
            ['button' => [
                'class' => 'btn btn-default',
                'type' => 'submit'
            ]], 'Test', '/button'
        ], $button);
        // button with bootstrap-type and bootstrap-size
        $button = $this->form->button('Test', [
            'bootstrap-type' => 'success',
            'bootstrap-size' => 'sm'
        ]);
        $this->assertHtml([
            ['button' => [
                'class' => 'btn btn-success btn-sm',
                'type' => 'submit'
            ]], 'Test', '/button'
        ], $button);
        // button with class
        $button = $this->form->button('Test', [
            'class' => 'btn btn-primary'
        ]);
        $this->assertHtml([
            ['button' => [
                'class' => 'btn btn-primary',
                'type' => 'submit'
            ]], 'Test', '/button'
        ], $button);
    }

    protected function _testInput($expected, $fieldName, $options = [], $debug = false)
    {
        $formOptions = [];
        if (isset($options['_formOptions'])) {
            $formOptions = $options['_formOptions'];
            unset($options['_formOptions']);
        }
        $this->form->create(null, $formOptions);
        $result = $this->form->control($fieldName, $options);
        $assert = $this->assertHtml($expected, $result, $debug);
    }

    public function testInput()
    {
        $fieldName = 'field';
        // Standard form
        $this->_testInput([
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['label' => [
                'class' => 'control-label',
                'for'   => $fieldName
            ]],
            \Cake\Utility\Inflector::humanize($fieldName),
            '/label',
            ['input' => [
                'type'  => 'text',
                'class' => 'form-control',
                'name'  => $fieldName,
                'id'    => $fieldName
            ]],
            '/div'
        ], $fieldName);
        // Horizontal form
        $this->_testInput([
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['label' => [
                'class' => 'control-label col-md-2',
                'for' => $fieldName
            ]],
            \Cake\Utility\Inflector::humanize($fieldName),
            '/label',
            ['div' => [
                'class' => 'col-md-10'
            ]],
            ['input' => [
                'type'  => 'text',
                'class' => 'form-control',
                'name'  => $fieldName,
                'id'    => $fieldName
            ]],
            '/div',
            '/div'
        ], $fieldName, [
            '_formOptions' => ['horizontal' => true]
        ]);
    }

    public function testInputText()
    {
        $fieldName = 'field';
        $this->_testInput([
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['label' => [
                'class' => 'control-label',
                'for'   => $fieldName
            ]],
            \Cake\Utility\Inflector::humanize($fieldName),
            '/label',
            ['input' => [
                'type'  => 'text',
                'class' => 'form-control',
                'name'  => $fieldName,
                'id'    => $fieldName
            ]],
            '/div'
        ], $fieldName, ['type' => 'text']);
    }

    public function testInputSelect()
    {
        $fieldName = 'color';
        $options   = [
            'type' => 'select',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green'
            ]
        ];

        $expected = [
            ['div' => ['class' => 'form-group select']],
            ['label' => ['class' => 'control-label', 'for' => 'color']],
            'Color',
            '/label',
            ['select' => ['name' => 'color', 'class' => 'form-control', 'id' => 'color']],
            ['option' => ['value' => 'red']], 'Red', '/option',
            ['option' => ['value' => 'blue']], 'Blue', '/option',
            ['option' => ['value' => 'green']], 'Green', '/option',
            '/select',
            '/div',
        ];
        $result = $this->form->control($fieldName, $options);
        $this->assertHtml($expected, $result);
    }

    public function testInputRadio()
    {
        $fieldName = 'color';
        $options   = [
            'type' => 'radio',
            'options' => [
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green'
            ]
        ];
        // Default
        $expected = [
            ['div' => [
                'class' => 'form-group'
            ]],
            ['label' => [
                'class' => 'control-label'
            ]],
            \Cake\Utility\Inflector::humanize($fieldName),
            '/label',
            ['input' => [
                'type' => 'hidden',
                'name' => $fieldName,
                'value' => '',
                'class' => 'form-control'
            ]]
        ];
        foreach ($options['options'] as $key => $value) {
            $expected = array_merge($expected, [
                ['div' => [
                    'class' => 'radio'
                ]],
                ['label' => [
                    'for'   => $fieldName . '-' . $key
                ]],
                ['input' => [
                    'type'  => 'radio',
                    'name'  => $fieldName,
                    'value' => $key,
                    'id'    => $fieldName . '-' . $key
                ]],
                $value,
                '/label',
                '/div'
            ]);
        }
        $expected = array_merge($expected, ['/div']);
        $this->_testInput($expected, $fieldName, $options);
        // Inline
        $options += [
            'inline' => true
        ];
        $expected = [
            ['div' => [
                'class' => 'form-group inlineradio'
            ]],
            ['label' => [
                'class' => 'control-label',
                'for' => $fieldName
            ]],
            \Cake\Utility\Inflector::humanize($fieldName),
            '/label',
            ['input' => [
                'type' => 'hidden',
                'name' => $fieldName,
                'value' => '',
                'class' => 'form-control'
            ]]
        ];
        foreach ($options['options'] as $key => $value) {
            $expected = array_merge($expected, [
                ['label' => [
                    'class' => 'radio-inline',
                    'for'   => $fieldName . '-' . $key
                ]],
                ['input' => [
                    'type'  => 'radio',
                    'name'  => $fieldName,
                    'value' => $key,
                    'id'    => $fieldName . '-' . $key
                ]],
                $value,
                '/label'
            ]);
        }
        $expected = array_merge($expected, ['/div']);
        $this->_testInput($expected, $fieldName, $options, true);
        // Horizontal
        $options += [
            '_formOptions' => ['horizontal' => true]
        ];
        $options['inline'] = false;
        $expected = [
            ['div' => [
                'class' => 'form-group'
            ]],
            ['label' => [
                'class' => 'control-label col-md-2'
            ]],
            \Cake\Utility\Inflector::humanize($fieldName),
            '/label',
            ['div' => [
                'class' => 'col-md-10'
            ]],
            ['input' => [
                'type' => 'hidden',
                'name' => $fieldName,
                'value' => '',
                'class' => 'form-control'
            ]]
        ];
        foreach ($options['options'] as $key => $value) {
            $expected = array_merge($expected, [
                ['div' => [
                    'class' => 'radio'
                ]],
                ['label' => [
                    'for'   => $fieldName . '-' . $key
                ]],
                ['input' => [
                    'type'  => 'radio',
                    'name'  => $fieldName,
                    'value' => $key,
                    'id'    => $fieldName . '-' . $key
                ]],
                $value,
                '/label',
                '/div'
            ]);
        }
        $expected = array_merge($expected, ['/div', '/div']);
        $this->_testInput($expected, $fieldName, $options);
        // Horizontal + Inline
        $options['inline'] = true;
        $expected = [
            ['div' => [
                'class' => 'form-group inlineradio'
            ]],
            ['label' => [
                'class' => 'control-label col-md-2',
                'for' => $fieldName
            ]],
            \Cake\Utility\Inflector::humanize($fieldName),
            '/label',
            ['div' => [
                'class' => 'col-md-10'
            ]],
            ['input' => [
                'type' => 'hidden',
                'name' => $fieldName,
                'value' => '',
                'class' => 'form-control'
            ]]
        ];
        foreach ($options['options'] as $key => $value) {
            $expected = array_merge($expected, [
                ['label' => [
                    'class' => 'radio-inline',
                    'for'   => $fieldName . '-' . $key
                ]],
                ['input' => [
                    'type'  => 'radio',
                    'name'  => $fieldName,
                    'value' => $key,
                    'id'    => $fieldName . '-' . $key
                ]],
                $value,
                '/label'
            ]);
        }
        $expected = array_merge($expected, ['/div', '/div']);
        $this->_testInput($expected, $fieldName, $options);
    }

    public function testInputCheckbox()
    {
        $fieldName = 'color';
        $options   = [
            'type' => 'checkbox',
        ];

        $expected = [
            ['div' => ['class' => 'checkbox ']],
            ['input' => ['type' => 'hidden', 'name' => 'color', 'class' => 'form-control', 'value' => '0']],
            ['label' => ['for' => 'color']],
            ['input' => ['type' => 'checkbox', 'name' => 'color', 'value' => '1', 'id' => 'color']],
            'Color',
            '/label',
            '/div',
        ];
        $result = $this->form->control($fieldName, $options);
        $this->assertHtml($expected, $result);
    }

    public function testInputGroup()
    {
        $fieldName = 'field';
        $options   = [
            'type' => 'text',
            'label' => false
        ];
        // Test with prepend addon
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['div' => [
                'class' => 'input-group'
            ]],
            ['span' => [
                'class' => 'input-group-addon'
            ]],
            '@',
            '/span',
            ['input' => [
                'type' => 'text',
                'class' => 'form-control',
                'name' => $fieldName,
                'id' => $fieldName
            ]],
            '/div',
            '/div'
        ];
        $this->_testInput($expected, $fieldName, $options + ['prepend' => '@']);
        // Test with append
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['div' => [
                'class' => 'input-group'
            ]],
            ['input' => [
                'type' => 'text',
                'class' => 'form-control',
                'name' => $fieldName,
                'id' => $fieldName
            ]],
            ['span' => [
                'class' => 'input-group-addon'
            ]],
            '.00',
            '/span',
            '/div',
            '/div'
        ];
        $this->_testInput($expected, $fieldName, $options + ['append' => '.00']);
        // Test with append + prepend
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['div' => [
                'class' => 'input-group'
            ]],
            ['span' => [
                'class' => 'input-group-addon'
            ]],
            '$',
            '/span',
            ['input' => [
                'type' => 'text',
                'class' => 'form-control',
                'name' => $fieldName,
                'id' => $fieldName
            ]],
            ['span' => [
                'class' => 'input-group-addon'
            ]],
            '.00',
            '/span',
            '/div',
            '/div'
        ];
        $this->_testInput(
            $expected,
            $fieldName,
            $options + ['prepend' => '$', 'append' => '.00']
        );
        // Test with prepend button
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['div' => [
                'class' => 'input-group'
            ]],
            ['span' => [
                'class' => 'input-group-btn'
            ]],
            ['button' => [
                'class' => 'btn btn-default',
                'type'  => 'submit'
            ]],
            'Go!',
            '/button',
            '/span',
            ['input' => [
                'type' => 'text',
                'class' => 'form-control',
                'name' => $fieldName,
                'id' => $fieldName
            ]],
            '/div',
            '/div'
        ];
        $this->_testInput(
            $expected,
            $fieldName,
            $options + ['prepend' => $this->form->button('Go!')]
        );

        // Test with append button
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['div' => [
                'class' => 'input-group'
            ]],
            ['input' => [
                'type' => 'text',
                'class' => 'form-control',
                'name' => $fieldName,
                'id' => $fieldName
            ]],
            ['span' => [
                'class' => 'input-group-btn'
            ]],
            ['button' => [
                'class' => 'btn btn-default',
                'type'  => 'submit'
            ]],
            'Go!',
            '/button',
            '/span',
            '/div',
            '/div'
        ];
        $this->_testInput(
            $expected,
            $fieldName,
            $options + ['append' => $this->form->button('Go!')]
        );
        // Test with append 2 button
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['div' => [
                'class' => 'input-group'
            ]],
            ['input' => [
                'type' => 'text',
                'class' => 'form-control',
                'name' => $fieldName,
                'id' => $fieldName
            ]],
            ['span' => [
                'class' => 'input-group-btn'
            ]],
            ['button' => [
                'class' => 'btn btn-default',
                'type'  => 'submit'
            ]],
            'Go!',
            '/button',
            ['button' => [
                'class' => 'btn btn-default',
                'type'  => 'submit'
            ]],
            'GoGo!',
            '/button',
            '/span',
            '/div',
            '/div'
        ];
        $this->_testInput($expected, $fieldName, $options + [
            'append' => [$this->form->button('Go!'), $this->form->button('GoGo!')]
        ]);
    }

    public function testAppendDropdown()
    {
        $fieldName = 'field';
        $options   = [
            'type' => 'text',
            'label' => false
        ];
        // Test with append dropdown
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['div' => [
                'class' => 'input-group'
            ]],
            ['input' => [
                'type' => 'text',
                'class' => 'form-control',
                'name' => $fieldName,
                'id' => $fieldName
            ]],
            ['span' => [
                'class' => 'input-group-btn'
            ]],
            ['div' => [
                'class' => 'btn-group'
            ]],
            ['button' => [
                'data-toggle' => 'dropdown',
                'class' => 'dropdown-toggle btn btn-default'
            ]],
            'Action',
            ['span' => ['class' => 'caret']], '/span',
            '/button',
            ['ul' => [
                'class' => 'dropdown-menu dropdown-menu-left'
            ]],
            ['li' => []], ['a' => ['href'  => '#']], 'Link 1', '/a', '/li',
            ['li' => []], ['a' => ['href'  => '#']], 'Link 2', '/a', '/li',
            ['li' => [
                'role' => 'separator',
                'class' => 'divider'
            ]], '/li',
            ['li' => []], ['a' => ['href'  => '#']], 'Link 3', '/a', '/li',
            '/ul',
            '/div',
            '/span',
            '/div',
            '/div'
        ];
        $this->_testInput($expected, $fieldName, $options + [
            'append' => $this->form->dropdownButton('Action', [
                $this->form->Html->link('Link 1', '#'),
                $this->form->Html->link('Link 2', '#'),
                'divider',
                $this->form->Html->link('Link 3', '#')
            ])
        ]);

        // Test with append dropup
        $expected = [
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['div' => [
                'class' => 'input-group'
            ]],
            ['input' => [
                'type' => 'text',
                'class' => 'form-control',
                'name' => $fieldName,
                'id' => $fieldName
            ]],
            ['span' => [
                'class' => 'input-group-btn'
            ]],
            ['div' => [
                'class' => 'btn-group dropup'
            ]],
            ['button' => [
                'data-toggle' => 'dropdown',
                'class' => 'dropdown-toggle btn btn-default'
            ]],
            'Action',
            ['span' => ['class' => 'caret']], '/span',
            '/button',
            ['ul' => [
                'class' => 'dropdown-menu dropdown-menu-left'
            ]],
            ['li' => []], ['a' => ['href'  => '#']], 'Link 1', '/a', '/li',
            ['li' => []], ['a' => ['href'  => '#']], 'Link 2', '/a', '/li',
            ['li' => [
                'role' => 'separator',
                'class' => 'divider'
            ]], '/li',
            ['li' => []], ['a' => ['href'  => '#']], 'Link 3', '/a', '/li',
            '/ul',
            '/div',
            '/span',
            '/div',
            '/div'
        ];
        $this->_testInput($expected, $fieldName, $options + [
            'append' => $this->form->dropdownButton('Action', [
                $this->form->Html->link('Link 1', '#'),
                $this->form->Html->link('Link 2', '#'),
                'divider',
                $this->form->Html->link('Link 3', '#')
            ], ['dropup' => true])
        ]);
    }

    public function testInputTemplateVars()
    {
        $fieldName = 'field';
        // Add a template with the help placeholder.
        $help = 'Some help text.';
        $this->form->setTemplates([
            'inputContainer' => '<div class="form-group {{type}}{{required}}">{{content}}<span>{{help}}</span></div>'
        ]);
        // Standard form
        $this->_testInput([
            ['div' => [
                'class' => 'form-group text'
            ]],
            ['label' => [
                'class' => 'control-label',
                'for'   => $fieldName
            ]],
            \Cake\Utility\Inflector::humanize($fieldName),
            '/label',
            ['input' => [
                'type'  => 'text',
                'class' => 'form-control',
                'name'  => $fieldName,
                'id'    => $fieldName
            ]],
            ['span' => true],
            $help,
            '/span',
            '/div'
        ], $fieldName, ['templateVars' => ['help' => $help]]);
    }

    public function testDateTime()
    {
        extract($this->dateRegex);

        $result = $this->form->dateTime('Contact.date');
        $expected = [
            ['input' => [
                'type' => 'datetime-local',
                'name' => 'Contact[date]',
                'class' => 'form-control',
                'step' => '1',
                'value' => ''
            ]],
        ];
        $this->assertHtml($expected, $result);

        // Test with input()
        $result = $this->form->control('Contact.date', ['type' => 'date']);
        $expected = [
            ['div' => [
                'class' => 'form-group date',
            ]],
            ['label' => ['class' => 'control-label', 'for' => 'contact-date']],
            'Date',
            '/label',
            ['input' => [
                'type' => 'date',
                'name' => 'Contact[date]',
                'class' => 'form-control',
                'id' => 'contact-date',
                'value' => ''
            ]],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testSubmit()
    {
        $this->form->horizontal = false;
        $result = $this->form->submit('Submit');
        $expected = [
            ['div' => ['class' => 'form-group']],
            ['input' => [
                'type' => 'submit',
                'class' => 'btn btn-default',
                'value' => 'Submit'
            ]],
            '/div'
        ];
        $this->assertHtml($expected, $result);

        // horizontal forms
        $this->form->horizontal = true;
        $result = $this->form->submit('Submit');
        $expected = [
            ['div' => ['class' => 'form-group']],
            ['div' => ['class' => 'col-md-offset-2 col-md-10']],
            ['input' => [
                'type' => 'submit',
                'class' => 'btn btn-default',
                'value' => 'Submit'
            ]],
            '/div',
            '/div'
        ];
        $this->assertHtml($expected, $result);
    }

    public function testCustomFileInput()
    {
        $this->form->setConfig('useCustomFileInput', true);
        $result = $this->form->file('Contact.picture');
        $expected = [
            ['input' => [
                'type' => 'file',
                'name' => 'Contact[picture]',
                'id' => 'Contact[picture]',
                'style' => 'display: none;',
                'onchange' => "document.getElementById('Contact[picture]-input').value = (this.files.length <= 1) ? (this.files.length ? this.files[0].name : '') : this.files.length + ' ' + 'files selected';"
            ]],
            ['div' => ['class' => 'input-group']],
            ['div' => ['class' => 'input-group-btn']],
            ['button' => [
                'class' => 'btn btn-default',
                'type' => 'button',
                'onclick' => "document.getElementById('Contact[picture]').click();"
            ]],
            __('Choose File'),
            '/button',
            '/div',
            ['input' => [
                'type' => 'text',
                'name' => 'Contact[picture-text]',
                'class' => 'form-control',
                'readonly' => 'readonly',
                'id' => 'Contact[picture]-input',
                'onclick' => "document.getElementById('Contact[picture]').click();"
            ]],
            '/div',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->form->file('Contact.picture', ['multiple' => true]);
        $expected = [
            ['input' => [
                'type' => 'file',
                'multiple' => 'multiple',
                'name' => 'Contact[picture]',
                'id' => 'Contact[picture]',
                'style' => 'display: none;',
                'onchange' => "document.getElementById('Contact[picture]-input').value = (this.files.length <= 1) ? (this.files.length ? this.files[0].name : '') : this.files.length + ' ' + 'files selected';"
            ]],
            ['div' => ['class' => 'input-group']],
            ['div' => ['class' => 'input-group-btn']],
            ['button' => [
                'class' => 'btn btn-default',
                'type' => 'button',
                'onclick' => "document.getElementById('Contact[picture]').click();"
            ]],
            __('Choose Files'),
            '/button',
            '/div',
            ['input' => [
                'type' => 'text',
                'name' => 'Contact[picture-text]',
                'class' => 'form-control',
                'readonly' => 'readonly',
                'id' => 'Contact[picture]-input',
                'onclick' => "document.getElementById('Contact[picture]').click();"
            ]],
            '/div',
        ];
        $this->assertHtml($expected, $result);
    }

    public function testUploadCustomFileInput()
    {
        $expected = [
            ['input' => [
                'type' => 'file',
                'name' => 'Contact[picture]',
                'id' => 'Contact[picture]',
                'style' => 'display: none;',
                'onchange' => "document.getElementById('Contact[picture]-input').value = (this.files.length <= 1) ? (this.files.length ? this.files[0].name : '') : this.files.length + ' ' + 'files selected';"
            ]],
            ['div' => ['class' => 'input-group']],
            ['div' => ['class' => 'input-group-btn']],
            ['button' => [
                'class' => 'btn btn-default',
                'type' => 'button',
                'onclick' => "document.getElementById('Contact[picture]').click();"
            ]],
            __('Choose File'),
            '/button',
            '/div',
            ['input' => [
                'type' => 'text',
                'name' => 'Contact[picture-text]',
                'class' => 'form-control',
                'readonly' => 'readonly',
                'id' => 'Contact[picture]-input',
                'onclick' => "document.getElementById('Contact[picture]').click();"
            ]],
            '/div',
        ];
        $this->form->setConfig('useCustomFileInput', true);

        $result = $this->form->file('Contact.picture');
        $this->assertHtml($expected, $result);

        $this->form->getView()->setRequest($this->form->getView()->getRequest()->withData('Contact.picture', [
            'name' => '', 'type' => '', 'tmp_name' => '',
            'error' => 4, 'size' => 0
        ]));
        $result = $this->form->file('Contact.picture');
        $this->assertHtml($expected, $result);

        $this->form->getView()->setRequest($this->form->getView()->getRequest()->withData(
            'Contact.picture',
            'no data should be set in value'
        ));
        $result = $this->form->file('Contact.picture');
        $this->assertHtml($expected, $result);
    }

    public function testFormSecuredFileControl()
    {
        $this->View->setRequest($this->View->getRequest()->withAttribute('formTokenData', [
            'unlockedFields' => [],
        ]));

        $this->form->setConfig('useCustomFileInput', true);
        $this->form->setConfig('useCustomFileInput', true);
        // Test with filename, see issues #56, #123
        $this->form->create();
        $this->form->file('picture');
        $this->form->file('Contact.picture');

        $tokenData = $this->form->getFormProtector()->buildTokenData();

        $this->assertSame('949a50880781bda6c5c21f4ef7e82548c682b7e8%3A', $tokenData['fields']);
    }
}
