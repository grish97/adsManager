<?php

namespace app;

use Google\AdsApi\AdManager\Util\v201902\AdManagerDateTimes;
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
        $this->size = null;

    }

    public function create() {
        $data = $_POST;

        $validator = new Validator();
        $error = $validator->validate($data);

        if(!empty($error)) {
            echo json_encode(['error' => $error]);
            return;
        }


        $this->createLineItem(
            $data['lineItemName'],
            $data['lineItem_orderId'],
            $data['lineItemType'],
            $data['lineItem_placementId'],
            $data['lineItem_size']
        );


        if($data['hasCreative'] !== 'false') {
            $name = $data['hasCreative'];
            $function = "create" . ucfirst($name). "Creative";

            $this->$function(
                $data[$name . '_creative_name'],
                $data[$name . '_creative_adId'],
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

    public function createLineItem($lineItemName, $lineItem_orderId, $lineIte_type, $lineItemPlacementId, $lineItem_size) {
        $size = _explode($lineItem_size);

        $this->size = $size;

        $lineItemService = $this->serviceFactory->createLineItemService($this->session);

        $inventoryTargeting = new InventoryTargeting();
        $inventoryTargeting->setTargetedPlacementIds([$lineItemPlacementId]);

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
        $lineItem->setLineItemType(LineItemType::STANDARD);
        $lineItem->setAllowOverbook(true);

        $creativePlaceholder = new CreativePlaceholder();
        $creativePlaceholder->setSize(new Size($size['width'], $size['height'], false));

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

        $result = $lineItemService->createLineItems([$lineItem])[0];

        $id = $result->getId();
        $name = $result->getName();

        $this->resultArr['lineItem'] = [
            'id' => $id,
            'name' => $name,
        ];

        $this->lineItemId = $result->getId();
    }

    public function createImageCreative($creativeName, $creative_advertiserId) {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $imageCreative = new ImageCreative();
        $imageCreative->setName($creativeName);
        $imageCreative->setAdvertiserId($creative_advertiserId);
        $imageCreative->setDestinationUrl('http://google.com');

        $size = new Size();
        $size->setWidth($this->size['width']);
        $size->setHeight($this->size['height']);
        $size->setIsAspectRatio(false);
        $imageCreative->setSize($size);

        $creativeAsset = new CreativeAsset();
        $creativeAsset->setFileName(300);
        $creativeAsset->setAssetByteArray(
            file_get_contents('https://goo.gl/3b9Wfh')
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

    public function createNativeCreative($name, $adId, $snippet = '') {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $nativeAppInstallTemplateId = 10004400;

        $size = new Size();
        $size->setWidth(1);
        $size->setHeight(1);

        $nativeAppInstallCreative = new TemplateCreative();
        $nativeAppInstallCreative->setName($name);
        $nativeAppInstallCreative->setAdvertiserId($adId);
        $nativeAppInstallCreative->setDestinationUrl(
            'https://play.google.com/store/apps/details?id=com.google.fpl.'
            . 'pie_noon'
        );

        $nativeAppInstallCreative->setSize($size);

        $nativeAppInstallCreative->setCreativeTemplateId($nativeAppInstallTemplateId);


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
    }

    public function createThirdPartyCreative($name, $adId, $snippet) {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $thirdParty = new ThirdPartyCreative();

        $thirdParty->setName($name);
        $thirdParty->setAdvertiserId($adId);

        $thirdParty->setSnippet($snippet);

        $thirdParty->setSize(new Size($this->size['width'], $this->size['height']));

        $creativePage = $creativeService->createCreatives([$thirdParty]);

        $result = $creativePage[0];

        $this->resultArr['thirdParty_creative'] = [
          'id' => $result->getId(),
          'name' => $result->getName()
        ];
    }

    public function createCustomCreative($name, $adId, $snippet) {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $custom = new CustomCreative();

        $custom->setName($name);
        $custom->setAdvertiserId($adId);

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

    }

    public function associate() {
        $licaService = $this->serviceFactory->createLineItemCreativeAssociationService($this->session);

        $lica = new LineItemCreativeAssociation();
        $lica->setLineItemId($this->lineItemId);
        $lica->setCreativeId($this->creativeId);

        $licaService->createLineItemCreativeAssociations([$lica]);
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
