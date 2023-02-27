<?php
/**  Get the data from govuk urls */
/**  //div[contains(@class, 'finder-results')]//li[contains(@class, 'gem-c-document-list__item')]/a (in the "href" attribute) */
/** 
* Scraper class
*
* Class to define an object for handling website scraping for Overton 
*
*/
class Scraper {
    public $dataStore;
    public $linksToProcess = [];

    function __construct() {

    }

    /** 
     * 
     * Go and get data from the given url using the given useragent 
     * -- Given time store this data in a cache file with a unique name
     * -- Each time called check the file first and the file date, if over x amount of time then repull fromm the url and save the data then return
     * -- Otherwise pull the data from a file instead and return this 
     * 
     * */
    function getData($url, $useragent='') {
        $options = array();

        if (strLen($useragent) > 0) {
            $options  = array('http' => array('user_agent' => $useragent));
        }
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        return $response;
    }

    /** 
     * Method to take in an array of urls, add a given delay between calls then process the data
     * return a new list of unique absolute urls 
     * 
     * --Given time this would be more dynamic, this method would just take a list of urls and return the requested xpath data
     * -- This method would then be called for each subsequent attempt to get data and create the DomDocument and DOMXpath returning the results of that
     * -- The other parts of the application would have their own methods to call this and then break the data down respectively
     * 
     * */
    function processUrls(array $urls, string $xpath, int $delay = 0) {
        try {
            if (count($urls) < 1) {
                throw new Exception('No urls added in array for processUrls');
            }
            foreach ($urls as $index=>$urldata) {
                $data = $this->getData($urldata['url'],$urldata['useragent']);

                // Create new domdocument from our data
                $domDoc = new DOMDocument();
                $domDoc->loadHTML($data);

                // Create pur xpath object and pass in pur xpath value
                $xpathdoc = new DOMXpath($domDoc);
                $elements = $xpathdoc->query($xpath);

                if (!is_null($elements)) {
                    foreach ($elements as $element) {
                        $href = $this->normaliseLink($element->getAttribute('href'),'http://www.gov.uk/');
                        // ensure we only have each link once
                        if (!in_array($href,$this->linksToProcess)) {
                            $this->linksToProcess[] = $href;
                        }
                    }
                } else {
                    throw New Exception('No elements returned using xpath to get a tags');
                }

                if ($delay > 0) {
                    sleep($delay);
                }
            }
            return true;
        }
        catch(Exception $e) {
            var_dump($e);
        }
    }

    /** 
     * Using the links acquired into linksToProcess get the data from each link
     * then take our array of xpaths and nodes and pull back the relevant data and store within this->DataStore
     * @return array 
    */
    function linkProcessor(array $xpathdata, string $useragent='') {
        try {
            if (count($this->linksToProcess) < 1) {
                throw new Exception('No links have been acquire yet, please acquire links through the processUrls method');
            }

            foreach ($this->linksToProcess as $link) {
                // Go and get the data
                $currData = [
                    'link' => $link
                ];
                $data = $this->getData($link,$useragent);

                // Create new domdocument from our data
                $domDoc = new DOMDocument();
                $domDoc->loadHTML($data);

                $xpathdoc = new DOMXpath($domDoc);

                $currData = [];
                foreach ($xpathdata as $id=>$xpath) {
                    $elements = $xpathdoc->query($xpath['xpath']);

                    if (!is_null($elements)) {
                        foreach ($elements as $element) {
                            // If its an attribute lets get the attribute value
                            if ($xpath['type'] == 'attribute') {
                                $currData[$xpath['name']][] = $element->getAttribute($xpath['val']);
                            }
                            elseif ($xpath['type'] == 'text') {
                                $currData[$xpath['name']][] = $element->nodeValue;
                            }
                        }

                    } else {
                        $currData[$xpath['name']][] = '';
                        // Add the xpath value thats failed so we can get the bottom of the issue faster
                        //throw New Exception('No elements returned using xpath to get our tags in linkProcessor - ' . $xpath['xpath']);
                    }
                }
                $this->dataStore[] = $currData;
            }
        }
        catch(Exception $e) {
            /** 
             * because of the nature of whhat we are doing here just var dumping the error causes issues 
            *    so this would be better as some kind of log or alert email 
            */
            var_dump($e);
        }

    }

    /** Normalise a given link to be absolute based on the given url */
    function normaliseLink($link,$url) {
       if (str_starts_with($link, '/')) {
        if(substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }
        $link = $url . $link;
       } 
       return $link;
    }
}
?>