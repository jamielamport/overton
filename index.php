<?php
error_reporting(E_ERROR);
include 'src/classes/scraper.php';

# Create a scraper object to loop through our 3 urls, get the data back we want and process that data
$govURLS = array(
    '1' => array(
        'url'=>'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest',
        'useragent'=>''),
    '2' => array(
        'url'=>'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=2',
        'useragent'=>''),
    '3' => array(
        'url'=>'https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=3',
        'useragent'=>'')
);


$scraper = new Scraper();

// First we are going to process the urls above and return in our scraper object a list of normalised absolute links
$scrapeddata = $scraper->processUrls($govURLS,"//div[contains(@class, 'finder-results')]//li[contains(@class, 'gem-c-document-list__item')]/a",2);

/** 
* setup our array of xpath data we want to pull from all our documents along with the attributes or nodes required 
*
* this could then be extended in the future 
* or this data could be pulled from a db with an admin tool to allow adding editing etc
*
* We could also add a value in the array to state whetehr we expect multiple values back. For now we will just store
*  all data in arrays thats returned to allow for multiple
*/
$xpaths = [
    1=>[
        'name' => 'title',
        'xpath' => "//meta[@property='og:title']", 
        'type' => 'attribute',
        'val' => 'content'
    ],
    2=>[
        'name' => 'authors',
        'xpath' => "//div[contains(@class, 'gem-c-metadata')]//a[contains(@class, 'govuk-link')]",
        'type' => 'text'
    ]
];

$scraper->linkProcessor($xpaths,'');

print_r($scraper->dataStore);

?>
