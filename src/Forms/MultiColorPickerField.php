<?php

namespace Sitelease\PaletteColorField\Forms;

use Sitelease\OpenCore\Utilities\StringUtils;
use Sitelease\LiveColorField\Forms\LiveColorField;

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\Control\Controller;
use SilverStripe\View\HTML;
use SilverStripe\View\Requirements;
use SilverStripe\Core\Convert;

use Sitelease\OpenCore\Forms\Fields\EnhancedKeyValueField;

/**
 * A field that lets you specify both a key AND a value for each row entry
 *
 * @author Benjamin Blake (sitelease.ca)
 */
class MultiColorPickerField extends EnhancedKeyValueField
{
    public function Field($properties = [])
    {
        if (Controller::curr() instanceof ContentController) {
            Requirements::javascript('silverstripe/admin: thirdparty/jquery/jquery.js');
        }
        Requirements::javascript('sitelease/sl-open-core: client/dist/js/fields/EnhancedKeyValueField.js');
        Requirements::css('sitelease/sl-open-core: client/dist/css/fields/EnhancedKeyValueField.css');

        $nameKey = $this->name.'[key][]';
        $nameVal = $this->name.'[val][]';
        $fields  = [];
        $keyOptions = $this->sourceKeys;
        $keyFieldPlaceholder = $this->getKeyFieldPlaceholder();
        $valueFieldPlaceholder = $this->getValueFieldPlaceholder();

        if ($this->value) {
            foreach ($this->value as $i => $v) {
                if ($this->readonly) {
                    $fieldAttr = [
                        'class' => 'mventryfield  mvkeyvalReadonly '.($this->extraClass() ? $this->extraClass() : ''),
                        'id' => $this->id().static::KEY_SEP.$i,
                        'name' => $nameKey,
                        'tabindex' => $this->getAttribute('tabindex')
                    ];

                    $keyField        = HTML::createTag('span', $fieldAttr, Convert::raw2xml($i));
                    $fieldAttr['id'] = $this->id().static::KEY_SEP.$v;
                    $valField        = HTML::createTag('span', $fieldAttr, Convert::raw2xml($v));
                    $fields[]        = $keyField.$valField;
                } else {
                    // If NOT readonly, create drop downs for selecting the field
                    if ($this->isReadonlyKeysEnabled()) {
                        $fieldAttr = [
                            'class' => 'mventryfield__readonly-label',
                        ];

                        $keyTitle = $i;
                        if (!empty($keyOptions) && array_key_exists($i, $keyOptions)) {
                            $keyTitle = $keyOptions[$i];
                        }

                        $keyField = HTML::createTag('span', $fieldAttr, Convert::raw2xml($keyTitle));
                        $keyField .= $this->createSelectList($i, $nameKey, $keyOptions, $i, $keyFieldPlaceholder, "mventryfield--key hidden");
                    } else {
                        $keyField = $this->createSelectList($i, $nameKey, $keyOptions, $i, $keyFieldPlaceholder, "mventryfield--key");
                    }
                    $valField = $this->createColorPicker($i, $nameVal, $i, $v, $valueFieldPlaceholder, "mventryfield--value");
                    
                    // If item removal is enabled, add a removal button to each item
                    if ($this->isItemRemovalEnabled()) {
                        $fields[] = $keyField.' '.$valField.' '.$this->getRemovalButtonMarkup();
                    } else {
                        $fields[] = $keyField.' '.$valField;
                    }
                }
            }
        } else {
            $i = -1;
        }

        // If this field isn't read only, and users can add new items
        if (!$this->readonly && $this->isItemCreationEnabled()) {
            // Add a "new item" interface
            $keyField = $this->createSelectList('new', $nameKey, $keyOptions, '', $keyFieldPlaceholder, "mventryfield--key");
            // $valField = $this->createSelectList('new', $nameVal, $valueOptions, '', $valueFieldPlaceholder, "mventryfield--value");
            $valField = $this->createColorPicker('new', $nameVal, $i, '', $valueFieldPlaceholder, "mventryfield--value");

            // If item removal is enabled, add a removal button to the "new item" markup
            if ($this->isItemRemovalEnabled()) {
                $fields[] = $keyField.' '.$valField.' '.$this->getRemovalButtonMarkup();
            } else {
                $fields[] = $keyField.' '.$valField;
            }
//          $fields[] = $this->createSelectList('new', $name, $this->source);
        }

        $listTagAttributes = [
            "id" => $this->id(),
            "class" => "multivaluefieldlist mvkeyvallist ".$this->extraClass(),
        ];

        $listTag = HTML::createTag(
            'ul', 
            $listTagAttributes, 
            '<li>'.implode(
                '</li><li>',
                $fields
            ).'</li>'
        );

        return $listTag;
    }
 
    protected function createColorPicker($number, $name, $cssVar, $value = '', $placeholder = '', $extraClasses = '')
    {
        $attrs = [
            'id' => $this->id().static::KEY_SEP.$number,
        ];

        $colorPickerField = LiveColorField::create($name, $cssVar, null, $value)
        ->setAttribute("placeholder", $placeholder)
        ->addExtraClass('text mventryfield mvtextfield '.$extraClasses." ".($this->extraClass() ? $this->extraClass() : ''));
        
        if ($this->isItemCreationEnabled()) {
            $colorPickerField->setAttribute("data-item-creation", "true");
        }

        if ($this->disabled) {
            $colorPickerField->setDisabled(true);
        }

        return $colorPickerField;
    }
    
    public function createReadonlyInput($attributes, $value)
    {
        return HTML::createTag('span', $attributes, Convert::raw2xml($value));
    }

    public function createInput($attributes, $value = null)
    {
        $attributes['value'] = $value;
        return HTML::createTag($this->tag, $attributes);
    }

    public function performReadonlyTransformation()
    {
        $new = clone $this;
        $new->setReadonly(true);
        return $new;
    }

    public function setValue($v, $data = null)
    {
        if (is_array($v)) {
            // we've been set directly via the post - lets convert things to an appropriate key -> value
            // structure
            if (isset($v['key'])) {
                $newVal = [];

                for ($i = 0, $c = count($v['key']); $i < $c; $i++) {
                    if (strlen($v['key'][$i])) {
                        $newVal[$v['key'][$i]] = $v['val'][$i];
                    }
                }

                $v = $newVal;
            }
        }

        if ($v instanceof MultiValueField) {
            $v = $v->getValues();
        }

        if (!is_array($v)) {
            $v = [];
        }

        parent::setValue($v);
    }
    
}
