<?php

namespace M2Content\UIComponents\Block\Adminhtml\Components;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Widget\Block\BlockInterface;

class ImagePicker extends Template implements BlockInterface
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    public function __construct(
        Context $context,
        ElementFactory $elementFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    public function prepareElementHtml(AbstractElement $element)
    {
        $config = $this->_getData('config');
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl(
            'cms/wysiwyg_images/index',
            [
                'target_element_id' => $element->getId(),
                'type' => 'file',
            ]
        );

        $picker = $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class);
        $picker
            ->setType('button')
            ->setClass('btn-picker')
            ->setDisabled($element->getReadonly())
            ->setLabel($config['button']['open'])
            ->setUniqId($uniqId)
            ->setOnClick(sprintf(
                'MediabrowserUtility.openDialog("%s", null, null, "Select Image")',
                $sourceUrl
            ));

        $input = $this->elementFactory->create(
            "text",
            [
                'data' => $element->getData()
            ]
        );
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-text admin__control-text");

        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $element->setData(
            'after_element_html',
            sprintf(
                '%s%s',
                $input->getElementHtml(),
                $picker->toHtml()
            )
        );

        return $element;
    }
}
