<?php
namespace Rejoiner\Acr\Controller\Addtocart;


class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    protected $_objectInterface;
    protected $_productRepository;
    protected $_logger;
    protected $_rejoinerHelper;
    protected $_cart;
    protected $_product;
    protected $_session;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Rejoiner\Acr\Helper\Data $rejoinerHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectInterface,

        \Magento\Checkout\Model\SessionFactory $session,
        \Magento\Checkout\Model\CartFactory $cart,
        \Magento\Catalog\Model\ProductFactory $product

    )
    {
        $this->_rejoinerHelper    = $rejoinerHelper;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectInterface   = $objectInterface;
        $this->_cart              = $cart;
        $this->_session           = $session;
        $this->_product           = $product;
        $this->_logger            = $objectInterface->get('\Psr\Log\LoggerInterface');
        parent::__construct($context);
    }


    public function execute()
    {
        if ($params = $this->getRequest()->getParams()) {
            $cartModel = $this->_cart->create();
            $cartModel->truncate();

            foreach ($params as $key => $product) {
                if ($product && is_array($product)) {
                    $productModel = $this->_product->create();
                    $productModel->load((int)$product['product']);
                    try {
                        $cartModel->addProduct($productModel, $product);
                        unset($params[$key]);
                    } catch (\Exception $e) {
                        $this->_rejoinerHelper->log($e->getMessage());
                    }
                }
            }
            $cartModel->save();
            $this->_session->create()->setCartWasUpdated(true);
        }
        $url = $this->_objectManager->get('\Magento\Framework\UrlInterface')->getUrl('checkout/cart/', ['updateCart' => true]);
        $this->getResponse()->setRedirect($url);
        return $this->_response;
    }
}