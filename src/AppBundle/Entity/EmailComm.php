<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Gedmo\Mapping\Annotation as Gedmo;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * EmailComm
 *
 * @ORM\Table(name="email_comm")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EmailCommRepository")
 */
class EmailComm
{
    
    const TYPE_CASE_DELETED = 1;
    const TYPE_OTHER = 2;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="recipient_email", type="string", length=255, nullable=false)
     * 
     * @Assert\NotBlank(
     *      message = "Recipient Email is required"
     * )
     */
    private $recipientEmail;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="audit", type="text", nullable=true)
     */
    private $audit;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email_subject", type="string", nullable=true, length=255)
     */
    private $emailSubject;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email_body", type="text", nullable=false)
     */
    private $emailBody;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email_response", type="text", nullable=false)
     */
    private $response;
        
    /** 
     * @var integer
     * 
     * @ORM\Column(name="comm_type", type="integer") 
     */
    private $commType;
    
    /** 
     * @ORM\Column(name="obj_id", type="integer") 
     */
    private $objId;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return EmailComm
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return EmailComm
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set recipientEmail
     *
     * @param string $recipientEmail
     *
     * @return EmailComm
     */
    public function setRecipientEmail($recipientEmail)
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    /**
     * Get recipientEmail
     *
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }

    /**
     * Set audit
     *
     * @param string $audit
     *
     * @return EmailComm
     */
    public function setAudit($audit)
    {
        $this->audit = $audit;

        return $this;
    }

    /**
     * Get audit
     *
     * @return string
     */
    public function getAudit()
    {
        return $this->audit;
    }

    /**
     * Set emailBody
     *
     * @param string $emailBody
     *
     * @return EmailComm
     */
    public function setEmailBody($emailBody)
    {
        $this->emailBody = $emailBody;

        return $this;
    }

    /**
     * Get emailBody
     *
     * @return string
     */
    public function getEmailBody()
    {
        return $this->emailBody;
    }

    /**
     * Set response
     *
     * @param string $response
     *
     * @return EmailComm
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get response
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set commType
     *
     * @param integer $commType
     *
     * @return EmailComm
     */
    public function setCommType($commType)
    {
        $this->commType = $commType;

        return $this;
    }

    /**
     * Get commType
     *
     * @return integer
     */
    public function getCommType()
    {
        return $this->commType;
    }

    /**
     * Set objId
     *
     * @param integer $objId
     *
     * @return EmailComm
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId
     *
     * @return integer
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set emailSubject
     *
     * @param string $emailSubject
     *
     * @return EmailComm
     */
    public function setEmailSubject($emailSubject)
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    /**
     * Get emailSubject
     *
     * @return string
     */
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }
}
