<?php
namespace Rejoiner\Acr\Model\Resource\Acr;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Rejoiner\Acr\Model\Acr', 'Rejoiner\Acr\Model\Resource\Acr');
    }
}