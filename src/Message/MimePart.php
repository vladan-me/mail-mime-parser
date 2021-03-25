<?php
/**
 * This file is part of the ZBateson\MailMimeParser project.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace ZBateson\MailMimeParser\Message;

use ZBateson\MailMimeParser\IMessage;
use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Header\HeaderContainer;
use ZBateson\MailMimeParser\Message\Factory\PartFilterFactory;

/**
 * Default implementation of IMimePart.
 *
 * @author Zaahid Bateson
 */
class MimePart extends ParentHeaderPart implements IMimePart
{
    public function __construct(
        IMessagePart $parent = null,
        PartStreamContainer $streamContainer = null,
        HeaderContainer $headerContainer = null,
        PartChildrenContainer $partChildrenContainer = null,
        PartFilterFactory $partFilterFactory = null
    ) {
        if ($streamContainer === null || $headerContainer === null || $partChildrenContainer === null || $partFilterFactory === null) {
            $di = MailMimeParser::getDependencyContainer();
            $headerContainer = $di['\ZBateson\MailMimeParser\Header\HeaderContainer'];
            $partChildrenContainer = $di['\ZBateson\MailMimeParser\Message\PartChildrenContainer'];
            $partFilterFactory = $di['\ZBateson\MailMimeParser\Message\Factory\PartFilterFactory'];

            $streamContainer = $di['\ZBateson\MailMimeParser\Message\PartStreamContainer'];
            $streamFactory = $di['\ZBateson\MailMimeParser\Stream\StreamFactory'];
            $streamContainer->setStream($streamFactory->newMessagePartStream($this));
        }
        parent::__construct(
            $streamContainer,
            $headerContainer,
            $partChildrenContainer,
            $partFilterFactory,
            $parent
        );
    }

    public function getFilename()
    {
        return $this->getHeaderParameter(
            'Content-Disposition',
            'filename',
            $this->getHeaderParameter(
                'Content-Type',
                'name'
            )
        );
    }

    public function isMime()
    {
        return true;
    }

    public function isTextPart()
    {
        return ($this->getCharset() !== null);
    }

    public function getContentType($default = 'text/plain')
    {
        return trim(strtolower($this->getHeaderValue('Content-Type', $default)));
    }

    public function getCharset()
    {
        $charset = $this->getHeaderParameter('Content-Type', 'charset');
        if ($charset === null || strcasecmp($charset, 'binary') === 0) {
            $contentType = $this->getContentType();
            if ($contentType === 'text/plain' || $contentType === 'text/html') {
                return 'ISO-8859-1';
            }
            return null;
        }
        return trim(strtoupper($charset));
    }

    public function getContentDisposition($default = 'inline')
    {
        return strtolower($this->getHeaderValue('Content-Disposition', $default));
    }

    public function getContentTransferEncoding($default = '7bit')
    {
        static $translated = [
            'x-uue' => 'x-uuencode',
            'uue' => 'x-uuencode',
            'uuencode' => 'x-uuencode'
        ];
        $type = strtolower($this->getHeaderValue('Content-Transfer-Encoding', $default));
        if (isset($translated[$type])) {
            return $translated[$type];
        }
        return $type;
    }

    public function getContentId()
    {
        return $this->getHeaderValue('Content-ID');
    }

    public function isMultiPart()
    {
        // casting to bool, preg_match returns 1 for true
        return (bool) (preg_match(
            '~multipart/.*~i',
            $this->getContentType()
        ));
    }

    public function isSignaturePart()
    {
        if ($this->parent === null || !$this->parent instanceof IMessage) {
            return false;
        }
        return $this->parent->getSignaturePart() === $this;
    }

    public function getPartByContentId($contentId)
    {
        $sanitized = preg_replace('/^\s*<|>\s*$/', '', $contentId);
        return $this->getPart(0, function (IMessagePart $part) use ($sanitized) {
            return strcasecmp($part->getContentId(), $sanitized) === 0;
        });
    }
}
