<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 * 
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 * 
 *                     @category  Mageplaza
 *                     @package   Mageplaza_Blog
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Controller\Adminhtml\Tag;

class Save extends \Mageplaza\Blog\Controller\Adminhtml\Tag
{
    /**
     * Backend session
     * 
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * JS helper
     * 
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * constructor
     * 
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Mageplaza\Blog\Model\TagFactory $tagFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Backend\Helper\Js $jsHelper,
        \Mageplaza\Blog\Model\TagFactory $tagFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        $this->backendSession = $backendSession;
        $this->jsHelper       = $jsHelper;
        parent::__construct($tagFactory, $registry, $resultRedirectFactory, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('tag');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $tag = $this->initTag();
            $tag->setData($data);
            $posts = $this->getRequest()->getPost('posts', -1);
            if ($posts != -1) {
                $tag->setPostsData($this->jsHelper->decodeGridSerializedInput($posts));
            }
            $this->_eventManager->dispatch(
                'mageplaza_blog_tag_prepare_save',
                [
                    'tag' => $tag,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $tag->save();
                $this->messageManager->addSuccess(__('The Tag has been saved.'));
                $this->backendSession->setMageplazaBlogTagData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'mageplaza_blog/*/edit',
                        [
                            'tag_id' => $tag->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }
                $resultRedirect->setPath('mageplaza_blog/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Tag.'));
            }
            $this->_getSession()->setMageplazaBlogTagData($data);
            $resultRedirect->setPath(
                'mageplaza_blog/*/edit',
                [
                    'tag_id' => $tag->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }
        $resultRedirect->setPath('mageplaza_blog/*/');
        return $resultRedirect;
    }
}
