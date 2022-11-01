<?php

namespace Sitelease\PaletteColorField\Tests\Forms;

use Sitelease\PaletteColorField\Forms\PaletteColorField;
use SilverStripe\Dev\SapphireTest;

class PaletteColorFieldTest extends SapphireTest
{
    public function testGetSourceValues()
    {
        $field = new PaletteColorField('test');
        $field->setSource([
            [
                'Title' => 'Red',
                'CSSClass' => 'red',
                'Color' => '#E51016',
            ],
            [
                'Title' => 'Blue',
                'CSSClass' => 'blue',
                'Color' => '#1F6BFE',
            ]
        ]);
        $this->assertEquals(['', 'red', 'blue'], $field->getSourceValues());
    }
}
