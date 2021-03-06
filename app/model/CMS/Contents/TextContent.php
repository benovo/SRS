<?php
/**
 * Date: 15.11.12
 * Time: 13:27
 * Author: Michal Májský
 */
namespace SRS\Model\CMS;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 *
 * @property string $text
 */
class TextContent extends \SRS\Model\CMS\Content implements IContent
{
    protected $contentType = 'textcontent';
    protected $contentName = 'Text';

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $text;


    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }


    public function addFormItems(\Nette\Application\UI\Form $form)
    {
        parent::addFormItems($form);
        $form->getElementPrototype()->onsubmit('tinyMCE.triggerSave()');
        $formContainer = $form[$this->getFormIdentificator()];
        $formContainer->addTextArea("text", 'Text')->setDefaultValue($this->text)->getControlPrototype()->class('tinyMCE');
        return $form;
    }

    public function setValuesFromPageForm(\Nette\Application\UI\Form $form)
    {
        parent::setValuesFromPageForm($form);
        $values = $form->getValues();
        $values = $values[$this->getFormIdentificator()];
        $this->text = $values['text'];
    }

    public function getContentName()
    {
        return $this->contentName;
    }


}
