<?php

namespace AerialShip\LightSaml\Model;

use AerialShip\LightSaml\Error\InvalidAssertionException;
use AerialShip\LightSaml\Error\InvalidXmlException;
use AerialShip\LightSaml\Helper;
use AerialShip\LightSaml\Protocol;


class Assertion implements GetXmlInterface, LoadFromXmlInterface
{
    /** @var string */
    protected $id;

    /** @var int */
    protected $issueInstant;

    /** @var string */
    protected $version = Protocol::VERSION_2_0;

    /** @var string */
    protected $issuer;

    /** @var Signature|null */
    protected $signature;

    /** @var Subject */
    protected $subject;

    /** @var int */
    protected $notBefore;

    /** @var int */
    protected $notOnOrAfter;

    /** @var string[] */
    protected $validAudience;

    /** @var Attribute[] */
    protected $attributes = array();

    /** @var AuthnStatement */
    protected $authnStatement;





    /**
     * @param string $id
     */
    public function setID($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getID() {
        return $this->id;
    }


    /**
     * @param string $name
     * @return Attribute|null
     */
    public function getAttribute($name) {
        return @$this->attributes[$name];
    }

    /**
     * @param Attribute $attribute
     * @return $this
     */
    public function addAttribute(Attribute $attribute) {
        $this->attributes[$attribute->getName()] = $attribute;
        return $this;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Attribute[]
     */
    public function getAllAttributes() {
        return $this->attributes;
    }

    /**
     * @param $issueInstant
     * @throws \InvalidArgumentException
     * @param int|string $issueInstant
     */
    public function setIssueInstant($issueInstant) {
        if (is_string($issueInstant)) {
            $issueInstant = Helper::parseSAMLTime($issueInstant);
        } else if (!is_int($issueInstant) || $issueInstant < 1) {
            throw new \InvalidArgumentException('Invalid IssueInstance');
        }
        $this->issueInstant = $issueInstant;
    }

    /**
     * @return int
     */
    public function getIssueInstant() {
        return $this->issueInstant;
    }

    /**
     * @param string $issuer
     */
    public function setIssuer($issuer) {
        $this->issuer = $issuer;
    }

    /**
     * @return string
     */
    public function getIssuer() {
        return $this->issuer;
    }

    /**
     * @param \AerialShip\LightSaml\Model\Subject $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Subject
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param int|string $notBefore
     * @throws \InvalidArgumentException
     */
    public function setNotBefore($notBefore) {
        if (is_string($notBefore)) {
            $notBefore = Helper::parseSAMLTime($notBefore);
        } else if (!is_int($notBefore) || $notBefore < 1) {
            throw new \InvalidArgumentException();
        }
        $this->notBefore = $notBefore;
    }

    /**
     * @return int
     */
    public function getNotBefore() {
        return $this->notBefore;
    }

    /**
     * @param int|string $notOnOrAfter
     * @throws \InvalidArgumentException
     */
    public function setNotOnOrAfter($notOnOrAfter) {
        if (is_string($notOnOrAfter)) {
            $notBefore = Helper::parseSAMLTime($notOnOrAfter);
        } else if (!is_int($notOnOrAfter) || $notOnOrAfter < 1) {
            throw new \InvalidArgumentException();
        }
        $this->notOnOrAfter = $notOnOrAfter;
    }

    /**
     * @return int
     */
    public function getNotOnOrAfter() {
        return $this->notOnOrAfter;
    }

    /**
     * @param \AerialShip\LightSaml\Model\Signature|null $signature
     */
    public function setSignature($signature) {
        $this->signature = $signature;
    }

    /**
     * @return \AerialShip\LightSaml\Model\Signature|null
     */
    public function getSignature() {
        return $this->signature;
    }

    /**
     * @param string[] $validAudience
     */
    public function setValidAudience(array $validAudience) {
        $this->validAudience = $validAudience;
    }

    /**
     * @return \string[]
     */
    public function getValidAudience() {
        return $this->validAudience;
    }

    /**
     * @param string $value
     */
    public function addValidAudience($value) {
        $this->validAudience[] = $value;
    }

    /**
     * @param string $version
     */
    public function setVersion($version) {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }

    /**
     * @param \AerialShip\LightSaml\Model\AuthnStatement $authnStatement
     */
    public function setAuthnStatement(AuthnStatement $authnStatement) {
        $this->authnStatement = $authnStatement;
    }

    /**
     * @return \AerialShip\LightSaml\Model\AuthnStatement
     */
    public function getAuthnStatement() {
        return $this->authnStatement;
    }




    protected function prepareForXml() {
        if (!$this->getID()) {
            $this->setId(Helper::generateID());
        }
        if (!$this->getIssueInstant()) {
            $this->setIssueInstant(time());
        }
        if (!$this->getIssuer()) {
            throw new InvalidAssertionException('Issuer not set in Assertion');
        }
        if (!$this->getSubject()) {
            throw new InvalidAssertionException('Subject not set in Assertion');
        }
        if (!$this->getNotBefore()) {
            $this->setNotBefore(time());
        }
        if (!$this->getNotOnOrAfter()) {
            $this->setNotOnOrAfter(time());
        }
        if (!$this->getAuthnStatement()) {
            $this->setAuthnStatement(new AuthnStatement());
        }
    }


    /**
     * @param \DOMNode $parent
     * @return \DOMElement
     */
    function getXml(\DOMNode $parent) {
        $this->prepareForXml();

        $doc = $parent instanceof \DOMDocument ? $parent : $parent->ownerDocument;
        $result = $doc->createElementNS(Protocol::NS_ASSERTION, 'saml:Assertion');
        $parent->appendChild($result);

        $result->setAttribute('ID', $this->getID());
        $result->setAttribute('Version', $this->getVersion());
        $result->setAttribute('IssueInstant', gmdate('Y-m-d\TH:i:s\Z', $this->getIssueInstant()));

        $issuerNode = $doc->createElement('Issuer', $this->getIssuer());
        $result->appendChild($issuerNode);

        $this->getSubject()->getXml($result);

        $conditionsNode = $doc->createElement('Conditions');
        $result->appendChild($conditionsNode);
        $conditionsNode->setAttribute('NotBefore', gmdate('Y-m-d\TH:i:s\Z', $this->getNotBefore()));
        $conditionsNode->setAttribute('NotOnOrAfter', gmdate('Y-m-d\TH:i:s\Z', $this->getNotOnOrAfter()));
        if ($this->getValidAudience()) {
            $audienceRestrictionNode = $doc->createElement('AudienceRestriction');
            $conditionsNode->appendChild($audienceRestrictionNode);
            foreach ($this->getValidAudience() as $v) {
                $audienceNode = $doc->createElement('Audience', $v);
                $audienceRestrictionNode->appendChild($audienceNode);
            }
        }

        $attributeStatementNode = $doc->createElement('AttributeStatement');
        $result->appendChild($attributeStatementNode);
        foreach ($this->getAllAttributes() as $attribute) {
            $attribute->getXml($attributeStatementNode);
        }

        $this->getAuthnStatement()->getXml($result);

        return $result;
    }

    /**
     * @param \DOMElement $xml
     * @throws \AerialShip\LightSaml\Error\InvalidXmlException
     * @return \DOMElement[]
     */
    function loadFromXml(\DOMElement $xml) {
        if ($xml->localName != 'Assertion' || $xml->namespaceURI != Protocol::NS_ASSERTION) {
            throw new InvalidXmlException('Expected Assertion element but got '.$xml->localName);
        }

        foreach (array('ID', 'Version', 'IssueInstant') as $name) {
            if (!$xml->hasAttribute($name)) {
                throw new InvalidXmlException('Missing Assertion attribute '.$name);
            }
            $method = 'set'.$name;
            $this->$method($xml->getAttribute($name));
        }

        $xpath = new \DOMXPath($xml instanceof \DOMDocument ? $xml : $xml->ownerDocument);
        $xpath->registerNamespace('saml', Protocol::NS_ASSERTION);

        $result = array();

        /** @var $node \DOMElement */
        for ($node = $xml->firstChild; $node !== NULL; $node = $node->nextSibling) {
            if ($node->localName == 'Issuer') {
                $this->setIssuer(trim($node->textContent));
            } else if ($node->localName == 'Subject') {
                $this->setSubject(new Subject());
                $result = array_merge($result, $this->getSubject()->loadFromXml($node));
            } else if ($node->localName == 'Conditions') {
                if ($node->hasAttribute('NotBefore')) {
                    $this->setNotBefore($node->getAttribute('NotBefore'));
                }
                if ($node->hasAttribute('NotOnOrAfter')) {
                    $this->setNotOnOrAfter($node->getAttribute('NotOnOrAfter'));
                }
                /** @var $list \DOMElement[] */
                $list = $xpath->query('./saml:AudienceRestriction/saml:Audience', $xml);
                foreach ($list as $a) {
                    $this->addValidAudience($a->textContent);
                }
            } else if ($node->localName == 'AttributeStatement') {
                /** @var $list \DOMElement[] */
                $list = $xpath->query('./saml:AttributeStatement/saml:Attribute', $xml);
                foreach ($list as $a) {
                    $attr = new Attribute();
                    $attr->loadFromXml($a);
                    $this->addAttribute($attr);
                }
            } else if ($node->localName == 'AuthnStatement') {
                $this->setAuthnStatement(new AuthnStatement());
                $result = array_merge($result, $this->getAuthnStatement()->loadFromXml($node));
            } else {
                $result[] = $node;
            }
        }

        return $result;
    }


}