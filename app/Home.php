<?php

namespace app;

use Google\AdsApi\AdManager\v201902\CompanyType;
use Google\AdsApi\AdManager\Util\v201902\StatementBuilder;
use Google\AdsApi\AdManager\v201902\InventoryService;
use Google\AdsApi\AdManager\v201902\Order;
use Google\AdsApi\AdManager\v201902\Placement;
use Google\AdsApi\AdManager\v201902\ServiceFactory;

class Home
{
    public $data;

    public function __construct($session) {
        $this->session = $session;
        $this->serviceFactory = new ServiceFactory();
        $this->getData();
    }

    public function index() {
//        echo view('home.home', ['data' => $this->data]);
        echo $this->generateTemplate('home', $this->data);
    }

    public function getData() {
//        $liS = $this->serviceFactory->createLineItemService()
        //ADVERTISER
        $companyService = $this->serviceFactory->createCompanyService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->where('type = :type')
            ->withBindVariableValue('type', CompanyType::ADVERTISER);

        $page = $companyService->getCompaniesByStatement(
            $statementBuilder->toStatement()
        );

        $company = $page->getResults();

        foreach($company as $key => $val) {
            $id = $val->getId();
            $name = $val->getName();

            $this->data['advertiserId'][] = [
                'id' => $id,
                'name' => $name
            ];
        }

        //ORDER
        $orderService = $this->serviceFactory->createOrderService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->where('isArchived = :isArchived')
            ->withBindVariableValue('isArchived', false);

        $orderPage = $orderService->getOrdersByStatement(
            $statementBuilder->toStatement()
        );

        $orders = $orderPage->getResults();

        foreach($orders as $key => $order) {
            $id = $order->getId();
            $name = $order->getName();
            $this->data['orderId'][$key] = [
                'id' => $id,
                'name' => $name
            ];
        }

        //AD UNIT ID
        $inventoryService = $this->serviceFactory->createInventoryService($this->session);

        $statementBuilder = (new StatementBuilder());


        $inventoryPage = $inventoryService->getAdUnitsByStatement(
            $statementBuilder->toStatement()
        );

        $adUnits = $inventoryPage->getResults();

        foreach($adUnits as $key => $adUnit) {
            $id = $adUnit->getId();
            $name = $adUnit->getName();

            $this->data['adUnitId'][$key] = [
                'id' => $id,
                'name' => $name
            ];
        }

        //CREATIVE PLACEHOLDER SIZE
        $creativePlaceholderSize = [];

        $lineItemService = $this->serviceFactory->createLineItemService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->orderBY('id ASC');

        $lineItemPage = $lineItemService->getLineItemsByStatement(
            $statementBuilder->toStatement()
        );

        $result = $lineItemPage->getResults();

        foreach ($result as $val) {
            foreach($val->getCreativePlaceholders() as $size) {
                $getSize = $size->getSize();

                $creativePlaceholderSize[] = $getSize->getWidth() .'x'. $getSize->getHeight();
            }
        };

        $this->data['creativePlaceholderSize'] = array_unique($creativePlaceholderSize);


    }

    public function generateTemplate($template, $data) {
        $file = BASE_PATH . 'public' . DIRECTORY_SEPARATOR . $template . '.php';

        if(file_exists($file)) {
            ob_start();
            extract($data);
            require "$file";
            return ob_get_clean();
        }

        return false;
    }
}