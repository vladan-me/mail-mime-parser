<?php
/**
 * This file is part of the ZBateson\MailMimeParser project.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace ZBateson\MailMimeParser\Parser\Part;

use ZBateson\MailMimeParser\Header\HeaderFactory;
use ZBateson\MailMimeParser\Stream\StreamFactory;
use ZBateson\MailMimeParser\Message\Factory\PartFilterFactory;
use ZBateson\MailMimeParser\Message\MimePart;
use ZBateson\MailMimeParser\Parser\BaseParser;
use ZBateson\MailMimeParser\Parser\ParserProxy;
use ZBateson\MailMimeParser\Parser\PartBuilder;

/**
 * Responsible for creating ParsedMimePart instances.
 *
 * @author Zaahid Bateson
 */
class ParsedMimePartFactory extends ParsedMessagePartFactory
{
    /**
     * @var \ZBateson\MailMimeParser\Header\HeaderFactory the HeaderFactory
     *      instance
     */
    protected $headerFactory;

    /**
     * @var PartFilterFactory an instance used for creating MimePart objects
     */
    protected $partFilterFactory;

    /**
     * @var ParsedPartChildrenContainerFactory
     */
    protected $parsedPartChildrenContainerFactory;

    public function __construct(
        StreamFactory $sdf,
        HeaderFactory $headerFactory,
        ParsedPartStreamContainerFactory $pscf,
        ParsedPartChildrenContainerFactory $ppccf,
        PartFilterFactory $pf,
        BaseParser $baseParser
    ) {
        parent::__construct($sdf, $pscf, $baseParser);
        $this->headerFactory = $headerFactory;
        $this->partFilterFactory = $pf;
        $this->parsedPartChildrenContainerFactory = $ppccf;
    }

    /**
     * Constructs a new MimePart object and returns it
     * 
     * @param PartBuilder $partBuilder
     * @return \ZBateson\MailMimeParser\Message\IMimePart
     */
    public function newInstance(PartBuilder $partBuilder, ParsedPartChildrenContainer $parentContainer = null)
    {
        $streamContainer = $this->parsedPartStreamContainerFactory->newInstance();
        
        $headerContainer = $this->headerFactory->newHeaderContainer($partBuilder->getHeaderContainer());
        $childrenContainer = $this->parsedPartChildrenContainerFactory->newInstance();

        $part = new MimePart(
            ($parentContainer !== null) ? $parentContainer->getPartChildContained()->getPart() : null,
            $streamContainer,
            $headerContainer,
            $childrenContainer,
            $this->partFilterFactory
        );

        $parserProxy = new ParserProxy($this->baseParser, $this->streamFactory);
        $parserProxy->init($partBuilder, $streamContainer, $childrenContainer);

        $streamContainer->setStream($this->streamFactory->newMessagePartStream($part));
        $streamContainer->setParsedStream($this->streamFactory->getLimitedPartStream($partBuilder->getStream(), $partBuilder));
        $part->attach($streamContainer);

        if ($parentContainer !== null) {
            $parentContainer->addParsedChild($childrenContainer->getPartChildContained());
        }

        return $part;
    }
}
