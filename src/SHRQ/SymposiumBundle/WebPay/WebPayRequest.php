<?php

class WebPayRequest
{
	var $privateKey;
	var $webPayUrl;
	var $responseUrl;
	var $merchantNumber;
	var $webPayOrder;
	var $merchantOrder;
	var $amount;

	public function setPrivateKey ($file, $passphrase)
	{
		$fp = fopen ($file, "r");
		$key = fread ($fp, filesize($file));
		fclose ($fp);
		if (!($this->privateKey = openssl_pkey_get_private ($key, $passphrase)))
		{
			echo "'$file' is not valid PEM private key (or passphrase is incorrect).";
			die;
		}
	}

	public function setOrderInfo ($webPayOrder, $merchantOrder, $price)
	{
		$this->webPayOrder = $webPayOrder;
		$this->merchantOrder = $merchantOrder;
		$this->amount = $price * 100;
	}

	public function setWebPayUrl ($url)
	{
		$this->webPayUrl = $url;
	}

	public function setResponseUrl ($responseUrl)
	{
		$this->responseUrl = $responseUrl;
	}

	public function setMerchantNumber ($merchantNumber)
	{
		$this->merchantNumber = $merchantNumber;
	}

	public function getParams ()
	{
		$params = array ();
		$params ['MERCHANTNUMBER'] = $this->merchantNumber;
		$params ['OPERATION'] = 'CREATE_ORDER';
		$params ['ORDERNUMBER'] = $this->webPayOrder;
		$params ['AMOUNT'] = $this->amount;
		$params ['CURRENCY'] = 978; // CZK = 203, EUR = 978, GBP = 826, HUF = 348, PLN = 985, RUB = 643 and USD = 840
		$params ['DEPOSITFLAG'] = 1;
		$params ['MERORDERNUM'] = $this->merchantOrder;
		//$params ['MD'] = '';
		$params ['URL'] = $this->responseUrl;

		$digestText = implode ('|', $params);
		openssl_sign ($digestText, $signature, $this->privateKey);
		$signature = base64_encode ($signature);
		$params ['DIGEST'] = $signature;

		return $params;
	}

	public function requestUrl ()
	{
		$params = $this->getParams ();
		$url = $this->webPayUrl . '?' . http_build_query ($params);
		return $url;
	}
}
