<?php
namespace ZBateson\MailMimeParser\Message\Part;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Psr7;

/**
 * UUEncodedPartFactoryTest
 * 
 * @group UUEncodedPartFactory
 * @group MessagePart
 * @covers ZBateson\MailMimeParser\Message\Part\UUEncodedPartFactory
 * @covers ZBateson\MailMimeParser\Message\Part\MessagePartFactory
 * @author Zaahid Bateson
 */
class UUEncodedPartFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $uuEncodedPartFactory;
    
    protected function setUp()
    {
        $mocksdf = $this->getMockBuilder('ZBateson\MailMimeParser\Stream\StreamDecoratorFactory')
            ->getMock();
        $mocksdf->expects($this->any())
            ->method('getLimitedPartStream')
            ->willReturn(Psr7\stream_for('test'));
        $psfmFactory = $this->getMockBuilder('ZBateson\MailMimeParser\Message\Part\PartStreamFilterManagerFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $psfm = $this->getMockBuilder('ZBateson\MailMimeParser\Message\Part\PartStreamFilterManager')
            ->disableOriginalConstructor()
            ->getMock();
        $psfmFactory
            ->method('newInstance')
            ->willReturn($psfm);
        
        $this->uuEncodedPartFactory = new UUEncodedPartFactory($mocksdf, $psfmFactory);
    }
    
    public function testNewInstance()
    {
        $partBuilder = $this->getMockBuilder('ZBateson\MailMimeParser\Message\Part\PartBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        
        $part = $this->uuEncodedPartFactory->newInstance(
            Psr7\stream_for('test'),
            $partBuilder
        );
        $this->assertInstanceOf(
            '\ZBateson\MailMimeParser\Message\Part\UUEncodedPart',
            $part
        );
    }
}
