<?php

namespace DropstockBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Site
 */
class Site
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $platform;

    /**
     * @var string
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $checked;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $crypt;

    /**
     * @var array
     */
    private $modules;

    /**
     * @var array
     */
    private $data;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Site
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Site
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set platform
     *
     * @param string $platform
     * @return Site
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform
     *
     * @return string 
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Site
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set checked
     *
     * @param \DateTime $checked
     * @return Site
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Get checked
     *
     * @return \DateTime 
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Site
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set crypt
     *
     * @param string $crypt
     * @return Site
     */
    public function setCrypt($crypt)
    {
        $this->crypt = $crypt;

        return $this;
    }

    /**
     * Get crypt
     *
     * @return string 
     */
    public function getCrypt()
    {
        return $this->crypt;
    }

    /**
     * Set modules
     *
     * @param array $modules
     * @return Site
     */
    public function setModules($modules)
    {
        $this->modules = $modules;

        return $this;
    }

    /**
     * Get modules
     *
     * @return array 
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Set data
     *
     * @param array $data
     * @return Site
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array 
     */
    public function getData()
    {
        return $this->data;
    }


  /**
   *  Set values
   **/
  public function set($key, $val)
  {
    $this->{$key} = $val;
  }

  public function setDefault(){
    $datetime = new \DateTime();
    $this->setChecked($datetime);
  }
}
