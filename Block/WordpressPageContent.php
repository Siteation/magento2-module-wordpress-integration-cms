<?php

declare(strict_types=1);

namespace Mooore\WordpressIntegrationCms\Block;

use Magento\Framework\View\Element\Template;
use Mooore\WordpressIntegrationCms\Model\RemotePageRepository;
use Magento\Cms\Model\Template\FilterProvider;
use Mooore\WordpressIntegrationCms\Processors\AfterHtmlProcessor;

class WordpressPageContent extends Template
{
    /**
     * @var RemotePageRepository
     */
    protected $remotePageRepository;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var AfterHtmlProcessor
     */
    protected $afterHtmlProcessor;

    public function __construct(
        Template\Context $context,
        RemotePageRepository $remotePageRepository,
        FilterProvider $filterProvider,
        AfterHtmlProcessor $afterHtmlProcessor,
        array $data = []
    ) {
        $this->remotePageRepository = $remotePageRepository;
        $this->filterProvider = $filterProvider;
        $this->afterHtmlProcessor = $afterHtmlProcessor;
        parent::__construct($context, $data);
    }

    public function toHtml()
    {
        $contentType = $this->getContentType() ?? 'page';
        $siteId = $this->getSiteId();
        $pageId = $this->getPageId();

        if (!$siteId || !$pageId) {
            throw new Exception(
                "Both Site ID and Page ID are required fields"
            );
        }

        $page = null;
        if ($contentType === 'page') {
            $page = $this->remotePageRepository->get($siteId, $pageId);
        }

        $this->setData('page', $page);

        $html = $page['content']['rendered'];

        $html = $this->afterHtmlProcessor->process($html, $siteId);

        $html = $this->filterProvider->getPageFilter()->filter($html);

        return $html;
    }
}
