<?php

namespace M2Content\UIComponents\Block\Adminhtml\Components;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Widget\Block\BlockInterface;

class Textarea extends Template implements BlockInterface
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
        $input = $this->elementFactory->create(
            "textarea",
            [
                'data' => $element->getData()
            ]
        );
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        $input->setClass("widget-option input-textarea admin__control-text");

        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }

        $element->setData(
            'after_element_html',
            $input->getElementHtml()
        );

        return $element;
    }
}
