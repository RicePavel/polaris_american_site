<?php

define('MAX_FILE_SIZE', 100000000); 

//@author Pavel Rice

// PHP Simple HTML DOM Parser
require('simple_html_dom.php');
require('PHPExcel.php');

$headArray = [];
$headArray[] = 'name'; 
$resultArray = getParsingResultNew('https://rzr.polaris.com/en-us/trail-sport/', $headArray);

$sheet = array(
    $headArray
);
foreach ($resultArray as $row) {
	$rowArray = array();
	foreach($headArray as $specName) {
		$rowArray[] = $row[$specName];
	}
	$sheet[] = $rowArray;
}
$doc = new PHPExcel();
$doc->setActiveSheetIndex(0);
$doc->getActiveSheet()->fromArray($sheet, null, 'A1');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="your_name.xls"');
header('Cache-Control: max-age=0');
$writer = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
$writer->save('specifications.xls');

return;

/*
$result = file_get_contents('https://rzr.polaris.com/en-us/trail-sport/');
//$result = file_get_contents('https://rzr.polaris.com/en-us/multi-terrain/');
$html = str_get_html($result);
$htmlOnePage = '';
$htmlSpecPage = '';
$i = 1;
$productsLinks = $html->find('.wholegood-listing-block__wholegood-item a');
foreach ($productsLinks as $linkElement) {
	$href = 'https://rzr.polaris.com' . $linkElement->href;
	echo $href . '<br/>';
	$resultOnePage = file_get_contents($href);
	$htmlOnePage = str_get_html($resultOnePage);
	foreach ($htmlOnePage->find('.wholegood-sub-navigation-menu__link-list a') as $menuLink) {		
		if (trim($menuLink->innertext) == 'Specs') {
			$resultSpecPage = file_get_contents('https://rzr.polaris.com' . $menuLink->href);
			$htmlSpecPage = str_get_html($resultSpecPage);
			echo 'spec page loaded <br/>';
			foreach ($htmlSpecPage->find('div.specs-full__spec') as $divSpec) {
				$head = $divSpec->find('.specs-full__spec-heading')[0]->innertext;
				$value = $divSpec->find('.specs-full__spec-value')[0]->innertext;
				echo $head . ' = ' . $value . '<br/>';
			}			
			$htmlSpecPage->clear();
			break;
		}
	}
	$i++;
	$htmlOnePage->clear();
	echo '<br/><br/>';
}
unset($htmlOnePage);
unset($htmlSpecPage);
$html->clear();
unset($html);
*/

function getParsingResultNew($url, &$headArray) {
	$resultArray = [];
	$result = file_get_contents($url);
	$html = str_get_html($result);
	$htmlOnePage = '';
	$htmlSpecPage = '';
	$i = 1;
	$productsLinks = $html->find('.wholegood-listing-block__wholegood-item a');
	foreach ($productsLinks as $linkElement) {
		$newArray = [];
		$href = 'https://rzr.polaris.com' . $linkElement->href;
		$name = $linkElement->find(".wholegood-listing-block__wholegood-name")[0]->innertext;
		$newArray['name'] = $name;
		$resultOnePage = file_get_contents($href);
		$htmlOnePage = str_get_html($resultOnePage);
		foreach ($htmlOnePage->find('.wholegood-sub-navigation-menu__link-list a') as $menuLink) {		
			if (trim($menuLink->innertext) == 'Specs') {
				$resultSpecPage = file_get_contents('https://rzr.polaris.com' . $menuLink->href);
				$htmlSpecPage = str_get_html($resultSpecPage);
				foreach ($htmlSpecPage->find('div.specs-full__spec') as $divSpec) {
					$specName = $divSpec->find('.specs-full__spec-heading')[0]->innertext;
					$specValue = $divSpec->find('.specs-full__spec-value')[0]->innertext;
					if (!in_array($specName, $headArray)) {
						$headArray[] = $specName;
					}
					$newArray[$specName] = $specValue;
				}			
				$htmlSpecPage->clear();
				break;
			}
		}
		$i++;
		$htmlOnePage->clear();
		$resultArray[] = $newArray;
	}
	unset($htmlOnePage);
	unset($htmlSpecPage);
	$html->clear();
	unset($html);
	return $resultArray;
}