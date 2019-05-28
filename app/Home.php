<?php

namespace app;

use Google\AdsApi\AdManager\v201902\CompanyType;
use Google\AdsApi\AdManager\Util\v201902\StatementBuilder;
use Google\AdsApi\AdManager\v201902\ServiceFactory;

class Home
{
    public $data;
    public $session;
    public $serviceFactory;

    public function __construct($session) {
        $this->session = $session;
        $this->serviceFactory = new ServiceFactory();
        $this->getData();
    }

    public function index() {
        echo $this->generateTemplate('home', $this->data);
    }

    public function getData() {
        //ADVERTISER ID
        $companyService = $this->serviceFactory->createCompanyService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->where('type = :type')
            ->limit(25)
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

        //ORDER ID
        $orderService = $this->serviceFactory->createOrderService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->where('isArchived = :isArchived')
            ->limit(25)
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

        //PLACEMENT ID
        $placementService = $this->serviceFactory->createPlacementService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->limit(25);

        $placementPage = $placementService->getPlacementsByStatement(
            $statementBuilder->toStatement()
        );

        $placements = $placementPage->getResults();

        foreach($placements as $key => $placement) {
            $id = $placement->getId();
            $name = $placement->getName();

            $this->data['placementId'][$key] = [
                'id' => $id,
                'name' => $name
            ];
        }

        //LINE ITEM
        $creativePlaceholderSize = [];

        $lineItemService = $this->serviceFactory->createLineItemService($this->session);

        $statementBuilder = (new StatementBuilder())
            ->where('status = :status')
            ->withBindVariableValue('status', 'DRAFT');

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
            require $file;
            return ob_get_clean();
        }

        return false;
    }
}