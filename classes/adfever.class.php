<?php


class AdFever {

	function __construct($args=array()) {
		$this->AdFever($args);
	}


	function AdFever( $args=array() ) {
		$this->kit_version = 0.2;

		$this->allowed_args = array(
			"sid",
			"start",      // the number of the search result to return first (used in paging)
			"size",       // the maximum number of results to return
			"adult",
            "aid"
            );

            $this->check_constructor($args);

            $this->defaults($args, "debug_level", 0);
            $this->defaults($args, "sid", 0);
            $this->defaults($args, "aid", 0);
            $this->defaults($args, "search_url", "http://pub.adfever.com/text/serveXMLPM");
            $this->defaults($args, "user_agent", urlencode($this->get_var('HTTP_USER_AGENT') ));
            $this->defaults($args, "referer", urlencode($this->get_var('SERVER_NAME') . $this->get_var('REQUEST_URI') ));
            $this->defaults($args, "ip", $this->get_var('REMOTE_ADDR') );
            $this->defaults($args, "start", 0 );
            $this->defaults($args, "size", 10 );
            $this->defaults($args, "adult", "NO");


            $this->total = 0;
            $this->return_total = 0;

            $this->errors = array();

            $this->check_session();

            $this->debug_level = 0;

	}

	function check_constructor ( $args=array() ) {
		$this->constructor_args = $args;

		while ($a = key ($args) ) {
			if (!in_array($a, $this->allowed_args) ) {
				$this->fatal_error(
					"$a is not a valid argument to AdFever() constructor"
				);
			}
			next($args);
		}
	}

	function defaults ($args, $key, $value) {
		empty($args[$key])  ? $this->$key = $value : $this->$key = $args[$key];
	}

	function check_session () {

		if ( headers_sent() ) {
			return;
		}

		if(isset($_COOKIE['afusid'])) {
			$this->usid = $_COOKIE['afusid'];
		}
		else {
			$this->usid = md5($this->aid.$this->user_agent.$this->ip.$this->get_var('REQUEST_TIME'));
			$this->usid = $this->usid.'.'.time();
			setcookie('afusid', $this->usid, time()+60*30, "/");
		}
	}

	function search ($query) {
		if (!$query) { return; }

		$this->enc_query = urlencode($query);
		$this->enc_user_agent= urlencode($this->user_agent);
		$this->listings = array();
		$this->meta = array();
		$this->query = urlencode(urldecode($query));

		$this->base_url = $this->search_url
		."?sid=$this->sid"
		."&ip=$this->ip"
		."&size=$this->size"
		."&start=$this->start"
		."&client_ua=$this->enc_user_agent"
		."&client_ref=$this->referer"
		."&usid=$this->usid"
		."&query=$this->enc_query";


		$content = @file($this->base_url);

		$this->xml_response = @join ("\n", $content);


		foreach ( explode("\n", $this->xml_response ) as $line) {
			$error_code = $this->parse_attr("error", "code", $line);
			$error = $this->parse_el("error", $line);
			if ($error != "" ) {
				$this->errors[] = $error;
			}
			if (is_numeric($error_code)) {
				$this->errors[] = $this->error_help($error_code);
			}

			$total = $this->parse_el("total", $line);

			if($total!="") {
				$this->total = $total;
			}

			$return_total = $this->parse_el("return_total", $line);

			if($return_total!="") {
				$this->return_total = $return_total;
			}


		}




		$i = 0;
		foreach ( explode("</result>", $this->xml_response ) as $line) {

			$this->listings[$i]['redirect_url'] = $this->parse_el("redirect_url", $line)."&usid=".$this->usid."&ip=".$this->ip;
			$this->listings[$i]['site_url'] = $this->parse_el("site_url", $line);
			$this->listings[$i]['title'] = $this->parse_el("title", $line);
			$this->listings[$i]['description'] = $this->parse_el("description", $line);
			$this->listings[$i]['is_adult'] = $this->parse_el("is_adult", $line);
			$i++;
		}
		array_pop($this->listings);

		if (!isset($this->meta['tot_count']) || !$this->meta['tot_count']) {
			$this->meta['tot_count'] = $i-1;
		}

	}

	function parse_attr($el, $attr, $str) {
		if ( preg_match("/<$el\s+$attr\s?=\s?['\"]?([^'\"\s]+)['\"\s]?>/i", $str, $match) ) {
			return trim($match[1]);
		}
	}

	function parse_el($el, $str) {
		if ( preg_match("/<$el>(.*)<\/$el>/i", $str, $match) ) {
			$match[1] = str_replace("<![CDATA[", "", $match[1]);
			$match[1] = str_replace("]]>", "", $match[1]);
			return trim($match[1]);
		}
	}

	function error_help ($error_code) {

		$ERRCODE_POLICY_REFERRER  = 110;
		$ERRCODE_POLICY_GEO       = 111;
		$ERRCODE_INTERNAL         = 112;
		$ERRCODE_NOAID            = 113;
		$ERRCODE_NOAUTH           = 114;
		$ERRCODE_NOQUERY          = 115;
		$ERRCODE_NOTYPE           = 116;
		$ERRCODE_NOIP             = 117;
		$ERRCODE_NOUID            = 118;
		$ERRCODE_BADIP            = 119;
		$ERRCODE_BADUID           = 120;
		$ERRCODE_BADQUERY         = 121;
		$ERRCODE_FORBIDDEN        = 122;
		$ERRCODE_NORESULTS        = 123;
		$ERRCODE_POLICY_OPENPROXY = 124;
		$ERRCODE_NOAUTH_NETWORK   = 125;
		$ERRCODE_NOAUTH_DOMAIN    = 126;


		switch ($error_code) {

			case $ERRCODE_POLICY_REFERRER;
			return "refering url '$this->referer' isn't allowed ";
			break;
			case $ERRCODE_POLICY_GEO;
			return "Traffic isn't allowed from the country in which $this->ip originates from";
			break;
			case $ERRCODE_INTERNAL;
			return "Results are temporarily unavailable, please try again later";
			break;
			case $ERRCODE_NOAID;
			return "The aid paramter is required, but was missing from the request ($this->base_url) ";
			break;
			case $ERRCODE_NOAUTH;
			return "AID $this->aid is not authorized to access $this->search_url, please check other parameters below";
			break;
			case $ERRCODE_NOQUERY;
			return "The query= parameter was missing from the request, please check parameters below.";
			break;
			case $ERRCODE_FORBIDDEN;
			return "The query parameter contains a term that is not allowed in our acceptable use policy.";
			break;
			case $ERRCODE_NORESULTS;
			return "No search results were found for '$this->query'";
			break;
			case $ERRCODE_POLICY_OPENPROXY;
			return "The client making the search request appears to be using a proxy, which violates our acceptable use policy.";
			break;
			case $ERRCODE_NOAUTH_NETWORK ;
			return "No access is allowed to the requested network '$this->network'";
			break;
			case $ERRCODE_NOAUTH_DOMAIN ;
			return "No access is allowed to the requested domain";
			break;
		}

		return "Error parsing xmlfeed";

	}

	function GetRetCount () { return $this->total; }
	function getTotalCount() { return $this->return_total; }
	function GetBid ($i) { return $this->listings[$i][bid];}
	function GetSiteURL($i) { return $this->listings[$i][site_url];}
	function GetRedirectURL($i) { return urlencode($this->listings[$i][redirect_url]); }
	function GetTitle($i) { return $this->listings[$i][title];}
	function GetDescription($i) { return $this->listings[$i][description];}

	function no_results () {
		$nomatch = false;

		if ($this->GetRetCount() == 0 ) {
			$nomatch = true;
		}

		if (! $this->GetRetCount() ) {
			$nomatch = true;
		}
		return $nomatch;
	}

	function fatal_error ($error) {

		if ($this->debug_level > 0) {
			$this->errors[] = $error;
			$this->debug();
		}

		die($error);
	}

	function or_equals ($var, $value) {
		if ($var == '' ) {
			return $value;
		}
		return $var;
	}


	function get_var ($key) {

		$superglobals = array(
			"_SERVER", "_COOKIE", "_GET", "_POST", "HTTP_POST_VARS", "HTTP_GET_VARS"
		);

		foreach ($superglobals as $name) {
			@eval("\$ar = \$$name;");
			if ( isset($ar[$key] ) ) {
				return $ar[$key];
			}
		}

		return false;

	}


	function has_errors () {
		return sizeof($this->errors);
	}


}
?>
