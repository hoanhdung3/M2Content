<?php

namespace M2Content\UIComponents\Block\Components;

use M2Content\ImageOptimizer\Service\ImageResizer;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Html\Link;

abstract class AbstractModel extends Link implements BlockInterface
{
    protected $imageResizer;

    protected $_template = 'widget/model.phtml';

    /**
     * @var string
     */
    protected $_defaultMode = 'left';

    public function getImageResizer(): ImageResizer
    {
        return $this->imageResizer;
    }

    public function getSizeWidthForImage($imageStyle = 'image')
    {
        switch (strtolower(trim($imageStyle))) {
            case 'icon_image':
                $width = $this->getIconImageSizeWidth();
                break;

            case 'thumbnail_image':
                $width = $this->getThumbnailImageResizeWidth();
                break;

            case 'small_image':
                $width = $this->getSmallImageResizeWidth();
                break;

            case 'medium_image':
                $width = $this->getMediumImageResizeWidth();
                break;

            case 'large_image':
                $width = $this->getLargeImageResizeWidth();
                break;

            case 'image':
            default:
                $width = $this->getImageResizeWidth();
                break;
        }

        return (int)$width;
    }

    public function getImageResizeWidth()
    {
        if (!$this->getData('image_resize_width')) {
            return 400;
        }

        return (int)$this->getData('image_resize_width');
    }

    public function getLargeImageResizeWidth()
    {
        if (!$this->getData('large_image_resize_width')) {
            return 1280;
        }

        return (int)$this->getData('large_image_resize_width');
    }

    public function getMediumImageResizeWidth()
    {
        if (!$this->getData('medium_image_resize_width')) {
            return 768;
        }

        return (int)$this->getData('medium_image_resize_width');
    }

    public function getSmallImageResizeWidth()
    {
        if (!$this->getData('small_image_resize_width')) {
            return 320;
        }

        return (int)$this->getData('small_image_resize_width');
    }

    public function getThumbnailImageResizeWidth()
    {
        if (!$this->getData('thumbnail_image_resize_width')) {
            return 80;
        }

        return (int)$this->getData('thumbnail_image_resize_width');
    }

    public function getIconImageSizeWidth()
    {
        if (!$this->getData('icon_image_resize_width')) {
            return 40;
        }

        return (int)$this->getData('icon_image_resize_width');
    }

    public function getImage()
    {
        $mediaUrl = $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return sprintf(
            '%s%s',
            $mediaUrl,
            $this->getData('image')
        );
    }

    public function getHeading()
    {
        if (!$this->getData('heading')) {
            throw new LocalizedException(__('Callout heading is not set.'));
        }

        return $this->getData('heading');
    }

    public function getSubtitle()
    {
        if (!$this->getData('subtitle')) {
            return '';
        }

        return $this->getData('subtitle');
    }

    public function getDescription()
    {
        if (!$this->getData('description')) {
            throw new LocalizedException(__('Callout description is not set.'));
        }

        return $this->getData('description');
    }

    public function getAllowedHtmlTags(): array
    {
        return ['p', 'a', 'strong', 'em', 'b', 'i', 'ul', 'ol', 'li', 'span', 'div', 'br'];
    }
}
