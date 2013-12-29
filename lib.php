<?php


function curl_trace_redirects($url, $timeout = 15) {

    $result = array();
    $ch = curl_init();

    $trace = true;
    $currentUrl = $url;

    $urlHist = array();
    //echo PHP_EOL." === Testing [$currentUrl]".PHP_EOL;
    while($trace && $timeout > 0 && !isset($urlHist[$currentUrl])) {
        $urlHist[$currentUrl] = true;

	//echo "     > Checking url $currentUrl".PHP_EOL;
	echo ".";
        curl_setopt($ch, CURLOPT_URL, $currentUrl);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $output = curl_exec($ch);
        if($output === false) {
            $traceItem = array(
                'errorno' => curl_errno($ch),
                'error' => curl_error($ch),
            );

            $trace = false;
        } else {
            $curlinfo = curl_getinfo($ch);

            if(isset($curlinfo['total_time'])) {
                $timeout -= $curlinfo['total_time'];
            }

            if(!isset($curlinfo['redirect_url'])) {
                $curlinfo['redirect_url'] = get_redirect_url($output);
            }

            if(!empty($curlinfo['redirect_url'])) {
                $currentUrl = $curlinfo['redirect_url'];
            } else {
                $trace = false;
            }

            $traceItem = $curlinfo;
        }

        $result[] = $traceItem;
    }

    if($timeout < 0) {
        $result[] = array('timeout' => $timeout);
    }

    curl_close($ch);

    return $result;
}

// apparently 'redirect_url' is not available on all curl-versions
// so we fetch the location header ourselves
function get_redirect_url($header) {
    if(preg_match('/^Location:\s+(.*)$/mi', $header, $m)) {
        return trim($m[1]);
    }

    return "";
}
