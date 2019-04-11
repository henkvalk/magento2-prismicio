<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: jeroen
 * Date: 10-4-19
 * Time: 17:41
 */

namespace Elgentos\PrismicIO\Controller\Route;

use Elgentos\PrismicIO\Model\Api;
use Elgentos\PrismicIO\Registry\CurrentDocument;
use Elgentos\PrismicIO\Registry\CurrentRoute;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;


class View extends Action implements HttpGetActionInterface, HttpPostActionInterface
{

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;
    /**
     * @var Api
     */
    private $api;
    /**
     * @var CurrentRoute
     */
    private $currentRoute;
    /**
     * @var CurrentDocument
     */
    private $currentDocument;
    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        Api $api,
        CurrentRoute $currentRoute,
        CurrentDocument $currentDocument,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);

        $this->resultForwardFactory = $resultForwardFactory;
        $this->api = $api;
        $this->currentRoute = $currentRoute;
        $this->currentDocument = $currentDocument;
        $this->pageFactory = $pageFactory;
    }

    /**
     * View CMS page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $uid = $this->getRequest()
                ->getParam('uid');

        $route = $this->currentRoute->getRoute();
        if (! $route) {
            return $this->forwardNoRoute();
        }

        if (! $uid) {
            return $this->forwardIndex();
        }

        $api = $this->api;
        if (! $api->isActive()) {
            return $this->forwardNoRoute();
        }

        $language = $api->getLanguage();

        $document = $api->create()
                ->getByUID($route->getContentType(), $uid, ['lang' => $language]);

        if (! $document) {
            return $this->forwardNoRoute();
        }

        $this->currentDocument->setDocument($document);

        var_dump($document->data->body);
        echo \Prismic\Dom\BlockGroup::asHtml([$document->data]);

        die;

        $page = $this->pageFactory->create();

        $page->addHandle([
            'prismicio_default',
            'prismicio_page_view',
            'prismicio_page_view_' . $route->getId(),
            'prismicio_page_view_' . $route->getContentType()
        ]);

        return $page;
    }

    /**
     * @return ResultInterface
     */
    public function forwardNoRoute(): ResultInterface
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('noroute');

        return $resultForward;
    }

    /**
     * @return ResultInterface
     */
    public function forwardIndex(): ResultInterface
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('index');

        return $resultForward->forward('index');
    }

}