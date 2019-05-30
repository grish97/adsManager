<?php

namespace app;

use Google\AdsApi\AdManager\Util\v201902\AdManagerDateTimes;
use Google\AdsApi\AdManager\v201902\AdUnit;
use Google\AdsApi\AdManager\v201902\AdUnitTargeting;
use Google\AdsApi\AdManager\v201902\ApiException;
use Google\AdsApi\AdManager\v201902\AssetCreativeTemplateVariableValue;
use Google\AdsApi\AdManager\v201902\BrowserTargeting;
use Google\AdsApi\AdManager\v201902\Company;
use Google\AdsApi\AdManager\v201902\CompanyType;
use Google\AdsApi\AdManager\v201902\CostType;
use Google\AdsApi\AdManager\v201902\CreativeAsset;
use Google\AdsApi\AdManager\v201902\CreativePlaceholder;
use Google\AdsApi\AdManager\v201902\CreativeRotationType;
use Google\AdsApi\AdManager\v201902\CustomCreative;
use Google\AdsApi\AdManager\v201902\Goal;
use Google\AdsApi\AdManager\v201902\GoalType;
use Google\AdsApi\AdManager\v201902\ImageCreative;
use Google\AdsApi\AdManager\v201902\InventoryTargeting;
use Google\AdsApi\AdManager\v201902\LineItemCreativeAssociation;
use Google\AdsApi\AdManager\v201902\LineItemType;
use Google\AdsApi\AdManager\v201902\Money;
use Google\AdsApi\AdManager\v201902\Order;
use Google\AdsApi\AdManager\Util\v201902\StatementBuilder;
use Google\AdsApi\AdManager\v201902\ServiceFactory;
use Google\AdsApi\AdManager\v201902\Size;
use Google\AdsApi\AdManager\v201902\StartDateTimeType;
use Google\AdsApi\AdManager\v201902\StringCreativeTemplateVariableValue;
use Google\AdsApi\AdManager\v201902\Targeting;
use Google\AdsApi\AdManager\v201902\Technology;
use Google\AdsApi\AdManager\v201902\TechnologyTargeting;
use Google\AdsApi\AdManager\v201902\TemplateCreative;
use Google\AdsApi\AdManager\v201902\ThirdPartyCreative;
use Google\AdsApi\AdManager\v201902\UnitType;
use Google\AdsApi\AdManager\v201902\UrlCreativeTemplateVariableValue;

class Services
{

    public function __construct($session) {
        $this->session = $session;
        $this->serviceFactory = new ServiceFactory();
        $this->resultArr = [];

        $this->lineItemId = null;
        $this->creativeId = null;
        $this->advertiserId = null;
        $this->size = null;
        $this->fileContent = null;
        $this->creativeName = null;
    }

    public function create() {
        $data = $_POST;

        $validator = new Validator();
        $error = $validator->validate($data);

        if(!empty($error)) {
            echo json_encode(['error' => $error]);
            return;
        }

        //AD UNIT TARGETING
        $lineItem_adUnitId = $data['lineItem_adUnitId'];

        $adUnitTargeting = new AdUnitTargeting();
        $adUnitTargeting->setAdUnitId($lineItem_adUnitId);

        //ADVERTISER ID
        $orderService = $this->serviceFactory->createOrderService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->where('id = : id')
            ->withBindVariableValue('id', $data['lineItem_orderId']);

        $page = $orderService->getOrdersByStatement($statementBuilder->toStatement());

        $advertiser = $page->getResults()[0];
        $this->advertiserId = $advertiser->getAdvertiserId();

        $this->createLineItem(
            $data['lineItemName'],
            $data['lineItem_orderId'],
            $data['lineItemType'],
            $adUnitTargeting,
            $data['lineItem_size']
        );

        if(isset($_FILES['file']['tmp_name'])) {
            $this->fileContent = uploadFile($_FILES['file']);
        }

        if($data['hasCreative'] !== 'false') {
            $this->creativeName = $data['hasCreative'];
            $name = $data['hasCreative'];
            $function = "create" . ucfirst($name). "Creative";

            $this->$function(
                $data[$name . '_creative_name'],
                $data[$name . '_snippet'] ?? ''
            );
        }

        echo json_encode($this->resultArr);
    }

    public function createAdvertiser() {
        $advertiserName = $_POST['name'];

        $companyService = $this->serviceFactory->createCompanyService($this->session);

        $company = new Company();
        $company->setName($advertiserName);
        $company->setType(CompanyType::ADVERTISER);

        $advertiserPage = $companyService->createCompanies([$company]);
        $result = $advertiserPage[0];

        $advertiserId = $result->getId();

        $this->resultArr['advertiser'] = [
            'id' => $result->getId(),
            'name' => $result->getName(),
        ];

        echo json_encode($advertiserId);
    }

    public function createOrder() {
        $orderName = $_POST['orderName'];
        $advertiserId = $_POST['order_advertiserId'];

        $traffickerId = '244855048';
        $orderService = $this->serviceFactory->createOrderService($this->session);
        $order = new Order();
        $order->setName($orderName);
        $order->setAdvertiserId($advertiserId);
        //SALESPERSON DEFAULT
        $order->setTraffickerId($traffickerId);

        $orderPage = $orderService->createOrders([$order]);

        $result = $orderPage[0];

        $orderId = $result->getId();

        $this->resultArr['order'] = [
            'id' => $result->getId(),
            'name' => $result->getName(),
        ];

        echo json_encode($orderId);
    }

    public function createLineItem($lineItemName, $lineItem_orderId, $lineIte_type, $adUnitTargeting, $lineItem_size) {
        $size = _explode($lineItem_size);

        $this->size = $size;

        $lineItemService = $this->serviceFactory->createLineItemService($this->session);

        $inventoryTargeting = new InventoryTargeting();

        $inventoryTargeting->setTargetedAdUnits([$adUnitTargeting]);

        $browserTechnology = new Technology();
        $browserTechnology->setId(500072);

        $browserTargeting = new BrowserTargeting();
        $browserTargeting->setBrowsers([$browserTechnology]);
        $technologyTargeting = new TechnologyTargeting();
        $technologyTargeting->setBrowserTargeting($browserTargeting);

        $targeting = new Targeting();
        $targeting->setInventoryTargeting($inventoryTargeting);
        $targeting->setTechnologyTargeting($technologyTargeting);

        $lineItem = new \Google\AdsApi\AdManager\v201902\LineItem();
        $lineItem->setName($lineItemName);
        $lineItem->setOrderId($lineItem_orderId);
        $lineItem->setTargeting($targeting);
        $lineItem->setLineItemType(LineItemType::PRICE_PRIORITY);
        $lineItem->setAllowOverbook(true);

        $creativePlaceholder = new CreativePlaceholder();
        $creativePlaceholder->setSize(new Size($this->size['width'], $this->size['height'], false));


        $lineItem->setCreativePlaceholders([$creativePlaceholder]);

        $lineItem->setCreativeRotationType(CreativeRotationType::EVEN);

        $lineItem->setStartDateTimeType(StartDateTimeType::IMMEDIATELY);
        $lineItem->setEndDateTime(
            AdManagerDateTimes::fromDateTime(
                new \DateTime('+1 month',
                    new \DateTimeZone('America/New_York')
                )
            )
        );

        $lineItem->setCostType(CostType::CPM);
        $lineItem->setCostPerUnit(new Money('USD', 2000000));

        $goal  = new Goal();
        $goal->setUnits(500000);
        $goal->setUnitType(UnitType::IMPRESSIONS);
        $goal->setGoalType(GoalType::LIFETIME);
        $lineItem->setPrimaryGoal($goal);


        try {
            $result = $lineItemService->createLineItems([$lineItem]);
        }catch(ApiException $exception) {
            $expParts = explode(' ', $exception->getMessage1());

            $expParts = str_replace('[', '', $expParts[0]);

            echo json_encode(['exception' => $expParts]);
            die();
        }

        $result = $result[0];

        $id = $result->getId();
        $name = $result->getName();

        $this->resultArr['lineItem'] = [
            'id' => $id,
            'name' => $name,
        ];

        $this->lineItemId = $result->getId();
    }

    public function createImageCreative($creativeName) {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);
        $imageCreative = new ImageCreative();
        $imageCreative->setName($creativeName);
        $imageCreative->setAdvertiserId($this->advertiserId);
        $imageCreative->setDestinationUrl('http://google.com');

        $size = new Size();
        $size->setWidth($this->size['width']);
        $size->setHeight($this->size['height']);
        $size->setIsAspectRatio(false);
        $imageCreative->setSize($size);

        $creativeAsset = new CreativeAsset();
        $creativeAsset->setFileName('image'. uniqid());
        $creativeAsset->setAssetByteArray(
            $this->fileContent
//            file_get_contents('https://goo.gl/3b9Wfh')
        );

        $imageCreative->setPrimaryImageAsset($creativeAsset);

        $creativePage = $creativeService->createCreatives([$imageCreative]);
        $result = $creativePage[0];

        $this->resultArr['image_creative'] = [
            'id' => $result->getId(),
            'name' => $result->getName()
        ];

        $this->creativeId = $result->getId();

        $this->associate();
    }

    public function createNativeCreative($name, $snippet = '') {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $nativeAppInstallTemplateId = 10004400;

        $size = new Size();
        $size->setWidth(1);
        $size->setHeight(1);
        $size->setIsAspectRatio(false);

        $nativeAppInstallCreative = new TemplateCreative();
        $nativeAppInstallCreative->setName($name);
        $nativeAppInstallCreative->setAdvertiserId($this->advertiserId);//4558636797
        $nativeAppInstallCreative->setDestinationUrl(
            'https://play.google.com/store/apps/details?id=com.google.fpl.'
            . 'pie_noon'
        );

        $nativeAppInstallCreative->setCreativeTemplateId($nativeAppInstallTemplateId);

        $nativeAppInstallCreative->setSize($size);

        $headLineVarValue = new StringCreativeTemplateVariableValue();
        $headLineVarValue->setUniqueName('Headline');
        $headLineVarValue->setValue('Pie Noon');
        $varValue = [$headLineVarValue];

        $bodyVarValue = new StringCreativeTemplateVariableValue();
        $bodyVarValue->setUniqueName('Body');
        $bodyVarValue->setValue('Hello Creative');
        $varValue[] = $bodyVarValue;

        //SET THE IMAGE ASSET
        $imageVarValue = new AssetCreativeTemplateVariableValue();
        $imageVarValue->setUniqueName('Image');
        $imageAsset = new CreativeAsset();
        $imageAsset->setFileName('image'.uniqid().'.png');
        $imageAsset->setAssetByteArray(
            file_get_contents(
                'https://lh4.ggpht.com/GIGNKdGHMEHFDw6TM2bgAUDKPQQRIReKZPqEpMeE'
                . 'hZOPYnTdOQGaSpGSEZflIFs0iw=h300'
            )
        );

        $imageVarValue->setAsset($imageAsset);
        $varValue[] = $imageVarValue;

        $priceVarVal = new StringCreativeTemplateVariableValue();
        $priceVarVal->setUniqueName('Price');
        $priceVarVal->setValue('Free');
        $varValue[] = $priceVarVal;

        //SET APP ICON IMAGE ASSET
        $appIconVarValue = new AssetCreativeTemplateVariableValue();
        $appIconVarValue->setUniqueName('Appicon');
        $appIconAsset = new CreativeAsset();
        $appIconAsset->setFileName('icon'.uniqid().'.png');
        $appIconAsset->setAssetByteArray(
            file_get_contents(
                'https://lh6.ggpht.com/Jzvjne5CLs6fJ1MHF-XeuUfpABzl0YNMlp4RpHn'
                . 'vPRCIj4--eTDwtyouwUDzVVekXw=w300'
            )
        );
        $appIconVarValue->setAsset($appIconAsset);
        $varValue[] = $appIconVarValue;

        //SET THE CALL TO ACTION TEXT
        $callToActionVarValue = new StringCreativeTemplateVariableValue();
        $callToActionVarValue->setUniqueName('Calltoaction');
        $callToActionVarValue->setValue('Install');

        $varValue[] = $callToActionVarValue;

        // Set the deep link URL.
        $deepLinkVarValue = new UrlCreativeTemplateVariableValue();
        $deepLinkVarValue->setUniqueName('DeeplinkclickactionURL');
        $deepLinkVarValue->setValue(' \'market://details?id=com.google.fpl.pie_noon\'');

        $varValue[] = $deepLinkVarValue;

        $nativeAppInstallCreative->setCreativeTemplateVariableValues(
            $varValue
        );

        $result = $creativeService->createCreatives(
            [$nativeAppInstallCreative]
        );

        $result = $result[0];

        $this->resultArr['native_creative'] = [
            'id' => $result->getId(),
            'name' => $result->getName()
        ];

        $this->creativeId = $result->getId();

        $this->associate();
    }

    public function createThirdPartyCreative($name, $snippet) {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $thirdParty = new ThirdPartyCreative();

        $thirdParty->setName($name);
        $thirdParty->setAdvertiserId($this->advertiserId);

        $thirdParty->setSnippet($snippet);

        $thirdParty->setSize(new Size($this->size['width'], $this->size['height']));

        $creativePage = $creativeService->createCreatives([$thirdParty]);

        $result = $creativePage[0];

        $this->resultArr['thirdParty_creative'] = [
          'id' => $result->getId(),
          'name' => $result->getName()
        ];

        $this->creativeId = $result->getId();

        $this->associate();
    }

    public function createCustomCreative($name, $snippet) {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $custom = new CustomCreative();

        $custom->setName($name);
        $custom->setAdvertiserId($this->advertiserId);

        $size = new Size();
        $size->setWidth($this->size['width']);
        $size->setHeight($this->size['height']);

        $custom->setSize($size);

        $custom->setHtmlSnippet($snippet);

        $page = $creativeService->createCreatives([$custom]);
        $result = $page[0];

        $this->resultArr['custom_creative'] = [
            'id' => $result->getId(),
            'name' => $result->getName()
        ];

        $this->creativeId = $result->getId();

        $this->associate();
    }

    public function associate() {
        $licaService = $this->serviceFactory->createLineItemCreativeAssociationService($this->session);

        $lica = new LineItemCreativeAssociation();

        $lica->setLineItemId($this->lineItemId);

        $lica->setCreativeId($this->creativeId);

        if($this->creativeName === 'native') {
            $lica->setSizes([new Size(1, 1, false)]);
        }

        try{
            $licaService->createLineItemCreativeAssociations([$lica]);
        }catch(ApiException $exception) {
             echo json_encode(['exception' => $exception->getMessage1()]);
             exit;
        }
    }

    public function delete() {
        $resId = [];
        $adService = $this->serviceFactory->createUserService($this->session);

        $statementBuilder = (new StatementBuilder())
           ->orderBy('id ASC');

        $orderPage = $adService->getUsersByStatement(
            $statementBuilder->toStatement()
        );

        $ads = $orderPage->getResults();

        foreach($ads as $ad) {
            $id = $ad->getId();
            $resId[] = [$id, $ad->getName()];
        }

        dd($resId);
//        $orderService->performOrderAction(new DeleteOrders(), $statementBuilder->toStatement());
    }
}
