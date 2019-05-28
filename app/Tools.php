<?php

namespace app;

use Google\AdsApi\AdManager\Util\v201902\AdManagerDateTimes;
use Google\AdsApi\AdManager\Util\v201902\ReportDownloader;
use Google\AdsApi\AdManager\v201902\Column;
use Google\AdsApi\AdManager\v201902\DateRangeType;
use Google\AdsApi\AdManager\v201902\Dimension;
use Google\AdsApi\AdManager\v201902\DimensionAttribute;
use Google\AdsApi\AdManager\v201902\ExportFormat;
use Google\AdsApi\AdManager\v201902\ReportDownloadOptions;
use Google\AdsApi\AdManager\v201902\ReportJob;
use Google\AdsApi\AdManager\v201902\ReportQuery;
use Google\AdsApi\AdManager\AdManagerSession;
use Google\AdsApi\AdManager\Util\v201902\StatementBuilder;
use Google\AdsApi\AdManager\v201902\ServiceFactory;
use OpenCloud\Image\Resource\ImageInterface;


class Tools
{
    public $session;
    public $serviceFactory;
    public $uniqueId;

    public function __construct(AdManagerSession $session)
    {
        $this->session = $session;
        $this->serviceFactory = new ServiceFactory();
        $this->uniqueId = uniqid();
    }

    public function index()
    {
        $data = $this->getOrder();

        echo $this->generateTemplate('index', $data);
    }

    public function getOrder()
    {
        $orderService = $this->serviceFactory->createOrderService($this->session);

        $statementBuilder = (new StatementBuilder)
        ->orderBy('id ASC');

        $page = $orderService->getOrdersByStatement(
            $statementBuilder->toStatement()
        );

        $results = $page->getResults();
        $result_arr = [];

        if ($results !== null) {
            foreach ($results as $key => $result) {
                $id = $result->getId();
                $name = $result->getName();

                $startDateTime = $result->getStartDateTime();

                $startDate = $startDateTime->getDate();

                $startDateData = $startDate->getDay() . '/' . $startDate->getMonth() . '/' . $startDate->getYear() . ' ' .
                    $startDateTime->getHour() . ':' . $startDateTime->getMinute() . ':' . $startDateTime->getSecond();

                $endDateTime = $result->getEndDateTime();

                $endDateData = null;

                if ($endDateTime !== null) {
                    $endDate = $endDateTime->getDate();

                    $endDateData = $endDate->getDay() . '/' . $endDate->getMonth() . '/' . $endDate->getYear() . ' ' .
                        $endDateTime->getHour() . ':' . $endDateTime->getMinute() . ':' . $endDateTime->getSecond();
                }

                $status = $result->getStatus();

                $currencyCode = $result->getCurrencyCode();

                $externalOrderId = $result->getExternalOrderId();

                $advertiserId = $result->getAdvertiserId();

                $agencyId = $result->getAgencyId();

                $creatorId = $result->getCreatorId();

                $traffickerId = $result->getTraffickerId();

                $data = [
                    'id' => $id,
                    'name' => $name,
                ];

                $result_arr[$key] = $data;
            }

            dd($result_arr);
        }
    }

    public function lineItemService($orderId)
    {
        $result_arr = [];
        $lineItemService = $this->serviceFactory->createLineItemService($this->session);

        $pageSize = StatementBuilder::SUGGESTED_PAGE_LIMIT;

        $statementBuilder = (new StatementBuilder)
            ->where('orderId = :orderId')
            ->limit($pageSize)
            ->withBindVariableValue('orderId', $orderId);

        $page = $lineItemService->getLineItemsBystatement(
            $statementBuilder->toStatement()
        );

        $results = $page->getResults();

        if ($results !== null) {
            foreach ($results as $key => $result) {
                $id = $result->getId();
                $name = $result->getName();

                $startDateTime = $result->getStartDateTime();

                $startDate = $startDateTime->getDate();

                $startDateData = $startDate->getDay() . '/' . $startDate->getMonth() . '/' . $startDate->getYear() . ' ' .
                    $startDateTime->getHour() . ':' . $startDateTime->getMinute() . ':' . $startDateTime->getSecond();

                $endDateTime = $result->getEndDateTime();

                $endDateData = null;

                if ($endDateTime !== null) {
                    $endDate = $endDateTime->getDate();

                    $endDateData = $endDate->getDay() . '/' . $endDate->getMonth() . '/' . $endDate->getYear() . ' ' .
                        $endDateTime->getHour() . ':' . $endDateTime->getMinute() . ':' . $endDateTime->getSecond();
                }

                $lineItemType = $result->getLineItemType();

                $costType = $result->getCostType();

                $discountType = $result->getDiscountType();

                $creativePlaceholders = $result->getCreativePlaceholders();
                $creativePlaceholderSize = [];

                foreach ($creativePlaceholders as $index => $val) {
                    $size = $val->getSize();
                    $width = $size->getWidth();
                    $height = $size->getHeight();

                    $size = $width . 'x' . $height;

                    $creativePlaceholderSize[$index] = $size;
                }

                $environmentType = $result->getEnvironmentType();

                $companionDeliveryOption = $result->getCompanionDeliveryOption();

                $status = $result->getStatus();
                $reservationStatus = $result->getReservationStatus();

                $creationDateTime = $result->getCreationDateTime();

                $creationDate = $creationDateTime->getDate();

                $creationDateData = $creationDate->getDay() . '/' . $creationDate->getMonth() . '/' . $creationDate->getYear() . ' ' .
                    $creationDateTime->getHour() . ':' . $creationDateTime->getMinute() . ':' . $creationDateTime->getSecond();

                $data = [
                    'id' => $id,
                    'name' => $name,
                    'startDateTime' => $startDateData,
                    'endDateTime' => $endDateData,
                    'lineItemType' => $lineItemType,
                    'costType' => $costType,
                    'discountType' => $discountType,
                    'creativePlaceholderSize' => $creativePlaceholderSize,
                    'environmentType' => $environmentType,
                    'companionDeliveryOption' => $companionDeliveryOption,
                    'status' => $status,
                    'reservationStatus' => $reservationStatus,
                    'creationDateTime' => $creationDateData
                ];

                $result_arr[$key] = $data;

            }

        }

        if(empty($result_arr)) {
            echo json_encode([]);
            return false;
        }

        echo json_encode($result_arr);

    }

    public function creativeService($lineItemId) {
        $resultArr = [];
        $creativeIdArr = [];

        $lineItemCreativeAssociation = $this->serviceFactory->createLineItemCreativeAssociationService($this->session);

        $creativeService = $this->serviceFactory->createCreativeService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->where('lineItemId = :lineItemId')
            ->withBindVariableValue('lineItemId', $lineItemId);

        $page = $lineItemCreativeAssociation->getLineItemCreativeAssociationsByStatement(
            $statementBuilder->toStatement()
        );

        $resultLineItemCreative = $page->getResults();

        if($resultLineItemCreative !== null) {
            foreach($resultLineItemCreative as $key => $val) {
                $creativeId = $val->getCreativeId();
                $creativeIdArr[$key] = $creativeId;
            }

            $creativeIdArr = implode(',', $creativeIdArr);

            $statementBuilderCreative = (new StatementBuilder)
                ->where("id in ($creativeIdArr)");

            $creativePage = $creativeService->getCreativesByStatement(
                $statementBuilderCreative->toStatement()
            );

            $resultCreative = $creativePage->getResults();

            if($resultCreative !== null) {
                foreach($resultCreative as $key => $val) {
                    $id = $val->getId();
                    $name = $val->getName();
                    $advertiserId = $val->getAdvertiserId();

                    $size = $val->getSize()->getWidth() . 'x' . $val->getSize()->getHeight();

                    $data =  [
                        'id' => $id,
                        'name' => $name,
                        'advertiserId' => $advertiserId,
                        'size' => $size
                    ];

                    $resultArr[$key] = $data;
                }
            }
        }

        echo json_encode($resultArr);
    }


    public function orderCreativeService($orderId) {
       $resultArr = [];
       $lineItemIdArr = [];
       $creativeIdArr = [];

       $lineItemService = $this->serviceFactory->createLineItemService($this->session);

       $lineItemCreativeAssocService = $this->serviceFactory->createLineItemCreativeAssociationService($this->session);

       $creativeService = $this->serviceFactory->createCreativeService($this->session);

       $lineItemStatementBuilder = (new StatementBuilder())
           ->where('orderId = :orderId')
           ->withBindVariableValue('orderId', $orderId);

       $lineItemPage = $lineItemService->getLineItemsByStatement(
           $lineItemStatementBuilder->toStatement()
       );


       $lineItems = $lineItemPage->getResults();

       if($lineItems !== null) {
           foreach ($lineItems as $key => $lineItem) {
               $id = $lineItem->getId();

               $lineItemIdArr[$key] = $id;
           }

           $lineItemIdArr = implode(',', $lineItemIdArr);

           $statementBuilderLineItem = (new StatementBuilder)
               ->where("lineItemId in ($lineItemIdArr)");

           $creativeAssocPage = $lineItemCreativeAssocService->getLineItemCreativeAssociationsByStatement(
               $statementBuilderLineItem->toStatement()
           );

           $creativeAssoc = $creativeAssocPage->getResults();

          if($creativeAssoc !== null) {
              foreach($creativeAssoc as $key => $val) {
                  $id = $val->getCreativeId();

                  $creativeIdArr[$key] = $id;
              }

              $creativeIdArr = implode(', ', $creativeIdArr);

              $statementBuilderCreative = (new StatementBuilder)
                  ->where("id in ($creativeIdArr)");

              $creativePage = $creativeService->getCreativesByStatement(
                  $statementBuilderCreative->toStatement()
              );

              $creatives = $creativePage->getResults();

              if($creatives !== null) {
                  foreach($creatives as $key => $creative) {
                      $id = $creative->getId();
                      $name = $creative->getName();

                      $getSize = $creative->getSize();

                      $size = $getSize->getWidth() . 'x' . $getSize->getHeight();

                      $advertiserId = $creative->getAdvertiserId();

                      $data = [
                          'id' => $id,
                          'name' => $name,
                          'size' => $size,
                          'advertiserId' => $advertiserId
                      ];

                      $resultArr[$key] = $data;
                  }
              }
          }


       }

       echo json_encode($resultArr);
    }


    public function getReport($orderId) {
        $downloadFileName = BASE_PATH . 'reports' . DIRECTORY_SEPARATOR . 'order' . $orderId .'.csv';

        if(!file_exists($downloadFileName)) {
            $this->downloadReport($orderId);
        }

        $data = $this->reportData($downloadFileName);

        echo json_encode($data);
    }

    public function reportData ($filePath) {
        $data = [];
        $handle = fopen($filePath, 'r');

        $columns = fgetcsv($handle, ', ');

        $columns = array_map(function($el) {
            $el = explode('.', $el)[1];
            return $el;
        } ,$columns);

        while (($row = fgetcsv($handle)) !== false) {
            $data[] = array_combine($columns, $row);
        }

        return $data ?? [];
    }

    public  function downloadReport($orderId) {
        set_time_limit(3000);

        $reportService = $this->serviceFactory->createReportService($this->session);

        //CREATE REPORT QUERY
        $reportQuery = new ReportQuery();

        $reportQuery->setDimensions([
            Dimension::ORDER_ID,
            Dimension::ORDER_NAME,
            Dimension::DATE
        ]);

        $reportQuery->setColumns([
            Column::TOTAL_LINE_ITEM_LEVEL_IMPRESSIONS,
            Column::TOTAL_LINE_ITEM_LEVEL_WITH_CPD_AVERAGE_ECPM,
            Column::TOTAL_LINE_ITEM_LEVEL_CPM_AND_CPC_REVENUE,
        ]);

        // Create statement to filter for an order.
        $statementBuilder = (new StatementBuilder())
            ->where("ORDER_ID = :orderId")
            ->withBindVariableValue('orderId', intVal($orderId));

        $reportQuery->setStatement(
            $statementBuilder->toStatement()
        );

        // Set the start and end dates or choose a dynamic date range type.

        $reportQuery->setDateRangeType(DateRangeType::CUSTOM_DATE);

        $reportQuery->setStartDate(
            AdManagerDateTimes::fromDateTime(
                new \DateTime('-6 days', new \DateTimeZone('America/New_York'))
            )->getDate()
        );

        $reportQuery->setEndDate(
            AdManagerDateTimes::fromDateTime(
                new \DateTime('now', new \DateTimeZone('America/New_York'))
            )->getDate()
        );

        // Create report job and start it.

        $reportJob = new ReportJob();
        $reportJob->setReportQuery($reportQuery);
        $reportJob = $reportService->runReportJob($reportJob);

        $reportDownloader = new ReportDownloader(
            $reportService,
            $reportJob->getId()
        );

        if ($reportDownloader->waitForReportToFinish()) {
            // Write to system temp directory by default.
            $filePath = BASE_PATH . 'reports' . DIRECTORY_SEPARATOR . 'order' . $orderId . '.csv';

            // Download option
            $options = new ReportDownloadOptions();
            $options->setExportFormat(ExportFormat::CSV_DUMP);
            $options->setUseGzipCompression(FALSE);

            $downloadUrl = $reportService->getReportDownloadUrlWithOptions(
                $reportJob->getId(),
                $options
            );

            file_put_contents($filePath, fopen($downloadUrl, 'r'));
        } else {
            return false;
        }
    }

    public function adUnitId() {
        $inventoryService = $this->serviceFactory->createInventoryService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->orderBy('id AS    C');

        $page = $inventoryService->getAdUnitsByStatement(
            $statementBuilder->toStatement()
        );

       echo $page->getResults()[0]->getId();
    }

    public function getNetwork() {
        $networkService = $this->serviceFactory->createNetworkService($this->session);

        $networkPage = $networkService->getAllNetworks();

        $networkCode = $networkPage[0]->getNetworkCode();

        return $networkCode;

    }


    public function generateTemplate($template, $data) {
        $file = BASE_PATH . 'public' . DIRECTORY_SEPARATOR . $template . '.php';

        if(file_exists($file)) {
            ob_start();
            extract($data);
            require $file;
            return ob_get_clean();
        }

        return false;
    }

}
