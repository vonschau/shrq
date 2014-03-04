<?php

namespace SHRQ\SymposiumBundle\WebPay;

class WebPayResponse
{
	var $publicKey;
	var $responseParams = array ();
	var $digest;

	public function setPublicKey ($file)
	{
		$fp = fopen($file, "r");
		$key = fread($fp, filesize ($file));
		fclose ($fp);
		if (!($this->publicKey = openssl_get_publickey($key)))
		{
			echo "'$file' is not valid PEM public key (or passphrase is incorrect).";
			die;
		}
	}

	public function setResponseParams ($params)
	{
		$this->responseParams ['OPERATION'] = isset ($params ['OPERATION']) ? $params ['OPERATION'] : '';
		$this->responseParams ['ORDERNUMBER'] = isset ($params ['ORDERNUMBER']) ? $params ['ORDERNUMBER'] : '';
		$this->responseParams ['MERORDERNUM'] = isset ($params ['MERORDERNUM']) ? $params ['MERORDERNUM'] : '';
		//$this->responseParams ['MD'] = isset ($params ['MD']) ? $params['MD'] : '';
		$this->responseParams ['PRCODE'] = isset ($params ['PRCODE']) ? $params ['PRCODE'] : '';
		$this->responseParams ['SRCODE'] = isset ($params ['SRCODE']) ? $params ['SRCODE'] : '';
		$this->responseParams ['RESULTTEXT'] = isset ($params ['RESULTTEXT']) ? $params ['RESULTTEXT'] : '';

		$this->digest = isset ($params ['DIGEST']) ? $params ['DIGEST'] : '';
	}

	public function verify ()
	{
		$data = implode('|', $this->responseParams);
		$digest = base64_decode ($this->digest);
		$ok = openssl_verify ($data, $digest, $this->publicKey);
		return (($ok == 1) && ($this->responseParams ['PRCODE'] == 0) && ($this->responseParams ['SRCODE'] == 0)) ? true : false;
	}

	public function orderWebpay () {return $this->responseParams ['ORDERNUMBER'];}
	public function orderMerchant () {return $this->responseParams ['MERORDERNUM'];}
}
