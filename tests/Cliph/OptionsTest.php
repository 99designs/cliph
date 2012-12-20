<?php

namespace Cliph;

use \Cliph\Options;

class OptionsTest extends \PHPUnit_Framework_TestCase
{
    private function assertNoErrors($options)
    {
        $this->assertEquals(count($options->errors()), 0,
            "There shouldn't be any parsing errors");
    }

    public function testBasicApi()
    {
        $options = new Options(array('testscript.php','-v','--after','2008-01-01'));
        $options->define(array('--after=2009-01-01','-v','--flag'));

        $this->assertTrue($options->has('-v'));
        $this->assertFalse($options->has('--flag'));
        $this->assertTrue($options->has('--after'));
        $this->assertEquals($options->value('--after'), '2008-01-01');
        $this->assertEquals($options->values('--after'), array('2008-01-01'));
        $this->assertNoErrors($options);
    }

    public function testDefaultValue()
    {
        $options = new Options(array('z.php'));
        $options->define(array('--blargh=24'));

        $this->assertFalse($options->has('--blargh'));
        $this->assertEquals($options->value('--blargh'), '24');
        $this->assertEquals($options->values('--blargh'), array('24'));
        $this->assertNoErrors($options);
    }

    public function testBareParameters()
    {
        $options = new Options(array('testscript.php','-v','myfilename'));
        $options->define(array('-v','--flag', ':filename'));

        $this->assertTrue($options->has('-v'));
        $this->assertTrue($options->has(':filename'));
        $this->assertEquals($options->value(':filename'), 'myfilename');
        $this->assertNoErrors($options);
    }

    public function testShortParametersCanBeAggregated()
    {
        $options = new Options(array('testscript.php','-vz','-q'));
        $options->define(array('-v','-x','-z','-q'));

        $this->assertTrue($options->has('-v'));
        $this->assertTrue($options->has('-z'));
        $this->assertTrue($options->has('-q'));
        $this->assertFalse($options->has('-x'));
        $this->assertNoErrors($options);
    }

    public function testShortParametersWithValues()
    {
        $options = new Options(array('testscript.php','-v', 'blargh','-v=meep'));
        $options->define(array('-v*=null'));

        $this->assertTrue($options->has('-v'));
        $this->assertEquals($options->values('-v'), array('blargh','meep'));
        $this->assertNoErrors($options);
    }

    public function testShortParametersWithValuesAndAggregates()
    {
        $options = new Options(array('testscript.php','-vxz','-r=meep'));
        $options->define(array('-r=false*','-v','-x','-z'));

        $this->assertTrue($options->has('-v'));
        $this->assertTrue($options->has('-x'));
        $this->assertTrue($options->has('-z'));
        $this->assertEquals($options->values('-r'), array('meep'));

        $options = new Options(array('x.php','-vrz'));
        $options
            ->define(array('-v*=false','-x','-z','-r'))
            ->parse()
            ;

        $this->assertEquals(count($options->errors()), 1);
    }

    public function testMultipleParamsToHas()
    {
        $options = new Options(array('x.php','file'));
        $options->define(array('--blargh',':file'));

        $this->assertFalse($options->has('-v'));
        $this->assertTrue($options->has(':file'));
        $this->assertTrue($options->has('-v',':file'));
        $this->assertNoErrors($options);
    }

    public function testRequiredParameters()
    {
        $options = new Options(array('x.php','-v'));
        $options->define(array('--blargh+','-v'));

        $this->assertFalse($options->has('--blargh'));
        $this->assertTrue($options->has('-v'));

        $this->assertEquals($options->errors(), array(
            'Parameter --blargh is required'
            ));
    }

    public function testOptionsWithEmptyDefaults()
    {
        $options = new Options(array('x.php'));
        $options->define(array('--blargh='));

        $this->assertFalse($options->has('--blargh'));
        $this->assertNull($options->value('--blargh'));
        $this->assertEquals($options->values('--blargh'), array());
        $this->assertNoErrors($options);
    }

    public function testOptionsWithNoValues()
    {
        $options = new Options(array('x.php'));
        $options->define(array('--blargh'));

        $this->assertFalse($options->has('--blargh'));
        $this->assertNull($options->value('--blargh'));
        $this->assertEquals($options->values('--blargh'), array());
        $this->assertNoErrors($options);
    }

    public function testOptionsValuesStartingWithDashes()
    {
        $options = new Options(array('x.php','-xv','-a','-3months'));
        $options->define(array('-x','-v','-a='));

        $this->assertTrue($options->has('-x'));
        $this->assertTrue($options->has('-v'));
        $this->assertTrue($options->has('-a'));

        $this->assertEquals($options->value('-a'), '-3months');
        $this->assertNoErrors($options);
    }

    public function testFetch()
    {
        $options = new Options(array('x.php', '-b=gralb'));
        $options->define(array('-b='));

        $this->assertEquals($options->fetch('-b', 'hello'), 'gralb');
        $this->assertEquals($options->fetch('-y', 'blarg'), 'blarg');
    }

    public function testAliases()
    {
        $options = new Options(array('x.php', '-s'));
        $options->define(array('-s,--long'));

        $this->assertTrue($options->has('-s'));
        $this->assertTrue($options->has('--long'));

        $options = new Options(array('x.php', '--long'));
        $options->define(array('--long,-s'));

        $this->assertTrue($options->has('-s'));
        $this->assertTrue($options->has('--long'));
    }

    public function testAliasesWithDefaults()
    {
        $options = new Options(array('x.php'));
        $options->define(array('-s,--long=llamas'));

        $this->assertFalse($options->has('-s'));
        $this->assertFalse($options->has('--long'));
        $this->assertEquals($options->value('-s'), 'llamas');
        $this->assertEquals($options->value('--long'),'llamas');
    }

    public function testMultipleOptionalParams()
    {
        $options = new Options(array('x.php', 'test1', 'test2'));
        $options->define(array(':param1', ':param2'));

        $this->assertTrue($options->has(':param1'));
        $this->assertTrue($options->has(':param2'));
        $this->assertEquals($options->value(':param1'), 'test1');
        $this->assertEquals($options->value(':param2'), 'test2');
    }

    public function testMultipleRecurringParamsAreGreedy()
    {
        $options = new Options(array('x.php', 'test1', 'test2'));
        $options->define(array(':param1+', ':param2'));

        $this->assertTrue($options->has(':param1'));
        $this->assertFalse($options->has(':param2'));
        $this->assertEquals($options->values(':param1'), array('test1', 'test2'));
        $this->assertEquals($options->values(':param2'), array());
    }
}
