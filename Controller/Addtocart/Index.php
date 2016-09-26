<?php
namespace Rejoiner\Acr\Controller\Addtocart;

use \Rejoiner\Acr\Helper\Data;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\ObjectManagerInterface;
use \Magento\Checkout\Model\SessionFactory;
use \Magento\Checkout\Model\CartFactory;
use \Magento\Catalog\Model\ProductFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $objectInterface;
    protected $productRepository;
    protected $logger;
    protected $rejoinerHelper;
    protected $cart;
    protected $product;
    protected $session;

    /**
     * @param \Rejoiner\Acr\Helper\Data $rejoinerHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectInterface
     * @param \Magento\Checkout\Model\SessionFactory $session
     * @param \Magento\Checkout\Model\CartFactory $cart
     * @param \Magento\Catalog\Model\ProductFactory $product
     */
    public function __construct(
        Data $rejoinerHelper,
        Session $checkoutSession,
        Context $context,
        PageFactory $resultPageFactory,
        ObjectManagerInterface $objectInterface,
        SessionFactory $session,
        CartFactory $cart,
        ProductFactory $product
    ) {
        $this->rejoinerHelper    = $rejoinerHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->objectInterface   = $objectInterface;
        $this->cart              = $cart;
        $this->session           = $session;
        $this->product           = $product;
        $this->logger            = $objectInterface->get('\Psr\Log\LoggerInterface');
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if ($params = $this->getRequest()->getParams()) {
            $cartModel = $this->cart->create();
            $cartModel->truncate();

            foreach ($params as $key => $product) {
                if ($product && is_array($product)) {
                    $productModel = $this->product->create();
                    $productModel->load((int)$product['product']);
                    try {
                        $cartModel->addProduct($productModel, $product);
                        unset($params[$key]);
                    } catch (\Exception $e) {
                        $this->rejoinerHelper->log($e->getMessage());
                    }
                }
            }
            if (isset($params['coupon_code'])) {
                $cartModel->getQuote()->setCouponCode($params['coupon_code'])->collectTotals();
            }
            $cartModel->save();
            $this->session->create()->setCartWasUpdated(true);
        }
        $url = $this->_objectManager->get('\Magento\Framework\UrlInterface')->getUrl('checkout/cart/', ['updateCart' => true]);
        $this->getResponse()->setRedirect($url);
        return $this->_response;
    }
}