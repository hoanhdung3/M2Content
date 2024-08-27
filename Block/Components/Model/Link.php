<?php

namespace M2Content\UIComponents\Block\Components\Model;

use M2Content\UIComponents\Block\Components\AbstractModel;
use M2Content\ImageOptimizer\Service\ImageResizer;
use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\ObjectManagerInterface;

class Link extends AbstractModel
{
    protected $imageResizer;

    protected $entityResource = null;

    protected $urlFinder;

    protected $objectManager;

    public function __construct(
        ImageResizer $imageResizer,
        $entityResource,
        UrlFinderInterface $urlFinder,
        ObjectManagerInterface $objectManager,
        Context $context,
        array $data = []
    ) {
        $this->imageResizer = $imageResizer;
        $this->urlFinder = $urlFinder;
        $this->objectManager = $objectManager;

        if (is_array($entityResource)) {
            if (empty($entityResource)) {
                $entityResource = null;
            } else {
                $entityResourceClass = reset($entityResource);
                if (!in_array($entityResourceClass, [null, "null", ""])) {
                    $entityResource = $this->objectManager->get($entityResourceClass);
                } else {
                    $entityResource = null;
                }
            }
        }

        $this->entityResource = $entityResource;

        parent::__construct($context, $data);
    }

    public function getImage()
    {
        if (!$this->getData('image')) {
            throw new LocalizedException(__('Model image is not set.'));
        }

        $resizedImageUrl = $this->getResizedImageUrlForImage('image');
        return $resizedImageUrl;
    }

    public function getResizedImageUrlForImage($imageStyle = 'image')
    {
        $resizer = $this->getImageResizer();
        $resizeWidth = $this->getSizeWidthForImage($imageStyle);
        $mediaPath = $this->getImagerHelper()->getPath('media');
        $filepath = $mediaPath . \DIRECTORY_SEPARATOR . $this->getData($imageStyle);

        $resizedImageUrl = $resizer->resize($filepath, $resizeWidth);
        return $resizedImageUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        try {
            $description = htmlspecialchars_decode(parent::getDescription());
            return $description;
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getHref()
    {
        if (!$this->getData('href')) {
            if (!$this->getData('id_path')) {
                throw new LocalizedException(__('Link id_path is not set.'));
            }

            $href = false;
            $rewriteData = $this->parseIdPath($this->getData('id_path'));
            $store = $this->hasData('store_id')
                ? $this->_storeManager->getStore($this->getData('store_id'))
                : $this->_storeManager->getStore();
            $filterData = [
                UrlRewrite::ENTITY_ID => $rewriteData[1],
                UrlRewrite::ENTITY_TYPE => $rewriteData[0],
                UrlRewrite::STORE_ID => $store->getId(),
            ];

            if (!empty($rewriteData[2]) && $rewriteData[0] == ProductUrlRewriteGenerator::ENTITY_TYPE) {
                $filterData[UrlRewrite::METADATA]['category_id'] = $rewriteData[2];
            }

            $rewrite = $this->urlFinder->findOneByData($filterData);
            if ($rewrite) {
                $href = $store->getUrl('', [
                    '_direct' => $rewrite->getRequestPath(),
                ]);

                if ($this->addStoreCodeParam($store, $href)) {
                    $href .= (strpos($href, '?') === false ? '?' : '&')
                        . '___store='
                        . $store->getCode();
                }
            }

            $this->setData('href', $href);
        }

        return $this->getData('href');
    }

    public function getLabel(): string
    {
        if (!$this->getData('anchor_text')) {
            if ($this->entityResource) {
                $idPath = explode('/', $this->getData('id_path'));
                if (isset($idPath[1])) {
                    $id = $idPath[1];
                    if ($id) {
                        $text = $this->entityResource->getAttributeRawValue(
                            $id,
                            'name',
                            $this->_storeManager->getStore()
                        );
                        $this->setData('anchor_text', $text);
                    }
                }
            } else {
                $this->setData('anchor_text', 'Go');
            }
        }

        return $this->getData('anchor_text');
    }

    protected function parseIdPath($idPath): array
    {
        $rewriteData = explode('/', $idPath);

        if (!isset($rewriteData[0]) || !isset($rewriteData[1])) {
            throw new LocalizedException(__('Incorrect id_path structure.'));
        }

        return $rewriteData;
    }

    protected function addStoreCodeParam(Store $store, string $url): bool
    {
        return $this->getData('store_id')
            && !$store->isUseStoreInUrl()
            && $store->getId() !==  $this->_storeManager->getStore()->getId()
            && strpos($url, '___store') === false;
    }

    protected function _toHtml()
    {
        if ($this->getHref()) {
            return parent::_toHtml();
        }

        return '';
    }
}
