<?php

namespace app;

use Google\AdsApi\AdManager\Util\v201902\AdManagerDateTimes;
use Google\AdsApi\AdManager\Util\v201902\ReportDownloader;
use Google\AdsApi\AdManager\v201902\BrowserTargeting;
use Google\AdsApi\AdManager\v201902\Column;
use Google\AdsApi\AdManager\v201902\Company;
use Google\AdsApi\AdManager\v201902\CompanyType;
use Google\AdsApi\AdManager\v201902\CostType;
use Google\AdsApi\AdManager\v201902\CreativeAsset;
use Google\AdsApi\AdManager\v201902\CreativePlaceholder;
use Google\AdsApi\AdManager\v201902\CreativeRotationType;
use Google\AdsApi\AdManager\v201902\DateRangeType;
use Google\AdsApi\AdManager\v201902\Dimension;
use Google\AdsApi\AdManager\v201902\DimensionAttribute;
use Google\AdsApi\AdManager\v201902\ExportFormat;
use Google\AdsApi\AdManager\v201902\Goal;
use Google\AdsApi\AdManager\v201902\GoalType;
use Google\AdsApi\AdManager\v201902\ImageCreative;
use Google\AdsApi\AdManager\v201902\InventoryStatus;
use Google\AdsApi\AdManager\v201902\InventoryTargeting;
use Google\AdsApi\AdManager\v201902\LineItemType;
use Google\AdsApi\AdManager\v201902\Money;
use Google\AdsApi\AdManager\v201902\Order;
use Google\AdsApi\AdManager\v201902\ReportDownloadOptions;
use Google\AdsApi\AdManager\v201902\ReportJob;
use Google\AdsApi\AdManager\v201902\ReportQuery;
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\Util\v201902\StatementBuilder;
use Google\AdsApi\AdManager\v201902\ServiceFactory;
use Google\AdsApi\AdManager\v201902\Size;
use Google\AdsApi\AdManager\v201902\StartDateTimeType;
use Google\AdsApi\AdManager\v201902\Targeting;
use Google\AdsApi\AdManager\v201902\Technology;
use Google\AdsApi\AdManager\v201902\TechnologyTargeting;
use Google\AdsApi\AdManager\v201902\UnitType;
use OpenCloud\Image\Resource\ImageInterface;

class LineItem
{
    public $dataId;
    public $advertiserId;
    public $session;
    public $serviceFactory;
    public $resultArr;

    public function __construct(AdManagerSession  $session) {
        $this->session = $session;
        $this->serviceFactory = new ServiceFactory();
        $this->dataId = $this->ids();
        $this->resultArr = [];
    }

    public function createLineItem($orderId) {
        $lineItemService = $this->serviceFactory->createLineItemService($this->session);

        $inventoryTargeting = new InventoryTargeting();
        $inventoryTargeting->setTargetedPlacementIds([$this->dataId['placementId']]);

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
        $lineItem->setName("Test Line Item #" . uniqId());
        $lineItem->setOrderId($orderId);
        $lineItem->setTargeting($targeting);
        $lineItem->setLineItemType(LineItemType::STANDARD);
        $lineItem->setAllowOverbook(true);

        $creativePlaceholder = new CreativePlaceholder();
        $creativePlaceholder->setSize(new Size(300, 250, false));

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
        $lineItem->setCostPerUnit(new Money($this->dataId['currencyCode'], 2000000));

        $goal  = new Goal();
        $goal->setUnits(500000);
        $goal->setUnitType(UnitType::IMPRESSIONS);
        $goal->setGoalType(GoalType::LIFETIME);
        $lineItem->setPrimaryGoal($goal);

        $result = $lineItemService->createLineItems([$lineItem])[0];

        $id = $result->getId();
        $name = $result->getName();
        $orderName = $result->getOrderName();

        $startDateTime = $result->getStartDateTime();

        $getDate = $startDateTime->getDate();

        $date = $getDate->getDay() .'/'. $getDate->getMonth() .'/'. $getDate->getYear();

        $this->resultArr['lineItem'] = [
            'id' => $id,
            'name' => $name,
        ];

        $this->createCreative();
    }


    public function createCreative() {
        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $imageCreative = new ImageCreative();
        $imageCreative->setName('Test Creative #' . uniqId());
        $imageCreative->setAdvertiserId($this->advertiserId);
        $imageCreative->setDestinationUrl('http://google.com');

        $size = new Size();
        $size->setWidth(600);
        $size->setHeight(315);
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

        $this->resultArr['creative'] = [
            'id' => $result->getId(),
            'name' => $result->getName()
        ];

        echo json_encode($this->resultArr);
    }

    public function createAdvertiser() {
        $companyService = $this->serviceFactory->createCompanyService($this->session);

        $company = new Company();
        $company->setName('Test Advertiser #' . uniqId());
        $company->setType(CompanyType::ADVERTISER);

        $advertiserPage = $companyService->createCompanies([$company]);
        $result = $advertiserPage[0];

        $this->resultArr['advertiser'] = [
          'id' => $result->getId(),
          'name' => $result->getName(),
        ];

        $advertiserId = $result->getId();

        $this->advertiserId = $advertiserId;
        $this->createOrder($advertiserId);
    }

    public function createOrder($advertiserId) {
        $traffickerId = '127264418';
        $orderService = $this->serviceFactory->createOrderService($this->session);
        $order = new Order();
        $order->setName('Test Order #' . uniqId());
        $order->setAdvertiserId($advertiserId);
        //SALESPERSON DEFAULT
        $order->setTraffickerId($traffickerId);

        $orderPage = $orderService->createOrders([$order]);
        $result = $orderPage[0];

        $this->resultArr['order'] = [
            'id' => $result->getId(),
            'name' => $result->getName(),
        ];

        $orderId = $result->getId();

        $this->createLineItem($orderId);
    }

    public function getRole($role) {
        $userService = $this->serviceFactory->createUserService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->orderBy('id ASC');

        $userPage = $userService->getUsersByStatement(
            $statementBuilder->toStatement()
        );

        dd($userPage->getResults());
    }


    public function ids() {
        //ORDER
        $orderService = $this->serviceFactory->createOrderService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->orderBy('id ASC');

        $orderPage = $orderService->getOrdersByStatement(
            $statementBuilder->toStatement()
        );

        $orderResult = $orderPage->getResults()[0];

        $orderId = $orderResult->getId();

        //PLACEMENT
        $placementService= $this->serviceFactory->createPlacementService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->where('status = :status')
            ->orderBy('id ASC')
            ->limit(1)
            ->withBindVariableValue('status', InventoryStatus::ACTIVE);

        $page = $placementService->getPlacementsByStatement(
            $statementBuilder->toStatement()
        );

        $placementId = $page->getResults()[0]->getId();

        //CURRENCY CODE
        $networkService = $this->serviceFactory->createNetworkService($this->session);

        $network = $networkService->getCurrentNetwork();

        $currencyCode = $network->getCurrencyCode();


        $data = [
            'orderId' => $orderId,
            'placementId' => $placementId,
            'currencyCode' => $currencyCode
        ];
        dd($data);
        return $data;
    }

}