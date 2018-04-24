<?php
namespace ZBateson\MailMimeParser\Message\Part;

use PHPUnit_Framework_TestCase;

/**
 * PartStreamFilterManagerFactoryTest
 * 
 * @group PartStreamFilterManagerFactory
 * @group MessagePart
 * @covers ZBateson\MailMimeParser\Message\Part\PartStreamFilterManagerFactory
 * @author Zaahid Bateson
 */
class PartStreamFilterManagerFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $partStreamFilterManagerFactory;
    
    protected function setUp()
    {
        $mocksdf = $this->getMockBuilder('ZBateson\MailMimeParser\Stream\StreamDecoratorFactory')
            ->getMock();
        $this->partStreamFilterManagerFactory = new PartStreamFilterManagerFactory(
            $mocksdf
        );
    }
    
    public function testNewInstance()
    {
        $manager = $this->partStreamFilterManagerFactory->newInstance();
        $this->assertInstanceOf(
            '\ZBateson\MailMimeParser\Message\Part\PartStreamFilterManager',
            $manager
        );
    }
}
