<div class="container mt-5 jumbotron">
    <!-- -->
    <form method="post" class="form">
        <h3 class="mt-4 mb-4">Line Item</h3>
        <div class="form-row">
            <div class="form-group col">
                <label for="lineItemName">Name</label>
                <input type="text" class="form-control" name="lineItemName" id="lineItemName" placeholder="Name">
            </div>
            <div class="form-group col">
                <label for="lineItem_orderId">Order</label>
                <select  id="lineItem_orderId" class="form-control" name="lineItem_orderId" data-name="order">
                    <option value="">Select Here</option>
                    <option value="newService">Add Order</option>
                    <?php foreach ($data['orderId'] as $val) : ?>
                        <option value="<?= $val['id']?>"><?= $val['name']?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="form-group col">
                <label for="lineItemType">Line Item Type</label>
                <select  id="lineItemType" class="form-control" name="lineItemType">
                    <option value="">Select Here</option>
                    <option value="SPONSORSHIP">SPONSORSHIP</option>
                    <option value="STANDARD">STANDARD</option>
                    <option value="NETWORK">NETWORK</option>
                    <option value="BULK">BULK</option>
                    <option value="PRICE_PRIORITY">PRICE_PRIORITY</option>
                    <option value="HOUSE">HOUSE</option>
                    <option value="LEGACY_DFP">LEGACY_DFP</option>
                    <option value="CLICK_TRACKING">CLICK_TRACKING</option>
                    <option value="ADSENSE">ADSENSE</option>
                    <option value="AD_EXCHANGE">AD_EXCHANGE</option>
                    <option value="BUMPER">BUMPER</option>
                    <option value="ADMOB">ADMOB</option>
                    <option value="PREFERRED_DEAL">PREFERRED_DEAL</option>
                    <option value="UNKNOWN">UNKNOWN</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col">
                <label for="lineItem_placementId">Placement</label>
                <select  id="lineItem_placementId" class="form-control" name="lineItem_placementId">
                    <option value="">Select Here</option>
                    <?php foreach ($data['placementId'] as $val) : ?>
                        <option value="<?= $val['id']?>"><?= $val['name']?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="form-group mr-2 col-2">
                <label for="lineItem_size">Size</label>
                <select id="lineItem_size" class="form-control" name="lineItem_size">
                    <option value="">Select Here</option>
                    <?php foreach ($data['creativePlaceholderSize'] as $size) : ?>
                        <option value="<?= $size?>"><?= $size?></option>
                    <?php endforeach ?>
                </select>
            </div>
        </div>
        <hr>
        <!--CREATIVE-->
        <h3 class="mr-3 mb-3">Creative</h3>
        <div class="form-group">
            <select  id="creative" class="form-control" name="hasCreative">
                <option value="false">Select Creative</option>
                <option value="image">Image</option>
                <option value="native">Native</option>
                <option value="custom">Custom</option>
                <option value="thirdParty">Third party</option>
            </select>
        </div>
        <button class="btn btn-success rounded-0 create" type="submit">Create</button>
    </form>

    <div class="creativeFields">
        <!--FIELDS FOR IMAGE CREATIVE-->
        <div class="imageCreative d-none">
            <h5 class="mt-3 mb-3">Image Creative</h5>
            <div class="form-row">
                <div class="form-group col">
                    <label for="image_creative_name">Name</label>
                    <input type="text" class="form-control" name="image_creative_name" id="image_creative_name" placeholder="Name">
                </div>
                <div class="form-group col">
                    <label for="image_creative_adId">Advertiser</label>
                    <select id="image_creative_adId" class="form-control" name="image_creative_adId">
                        <option value="">Select Here</option>
                        <?php foreach ($data['advertiserId'] as $val) : ?>
                            <option value="<?= $val['id']?>"><?= $val['name']?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
        </div>
        <!--NATIVE CREATIVE-->
        <div class="nativeCreative d-none">
            <h5 class="mt-3 mb-3">Native Creative</h5>
            <div class="form-row">
                <div class="form-group col">
                    <label for="native_creative_name">Name</label>
                    <input type="text" class="form-control" name="native_creative_name" id="native_creative_name" placeholder="Name">
                </div>
                <div class="form-group col">
                    <label for="native_creative_adId">Advertiser</label>
                    <select id="native_creative_adId" class="form-control" name="native_creative_adId">
                        <option value="">Select Here</option>
                        <option value="" class="newService">Add Order</option>
                        <?php foreach ($data['advertiserId'] as $val) : ?>
                            <option value="<?= $val['id']?>"><?= $val['name']?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>
        </div>
        <!--CUSTOM CREATIVE-->
        <div class="customCreative d-none">
            <h5 class="mt-3 mb-3">Custom Creative</h5>
            <div class="form-row">
                <div class="form-group col">
                    <label for="custom_creative_name">Name</label>
                    <input type="text" class="form-control" name="custom_creative_name" id="custom_creative_name" placeholder="Name">
                </div>
                <div class="form-group col">
                    <label for="custom_creative_adId">Advertiser</label>
                    <select id="custom_creative_adId" class="form-control" name="custom_creative_adId">
                        <option value="">Select Here</option>
                        <?php foreach ($data['advertiserId'] as $val) : ?>
                            <option value="<?= $val['id']?>"><?= $val['name']?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="md-form">
                    <label for="htmlSnippet">Html Snippet</label>
                    <textarea id="htmlSnippet" class="md-textarea form-control" rows="2" name="custom_snippet"></textarea>
                </div>
            </div>
        </div>
        <!--THIRD PARTY CREATIVE-->
        <div class="thirdPartyCreative d-none">
            <h5 class="mt-3 mb-3">Third Party Creative</h5>
            <div class="form-row">
                <div class="form-group col">
                    <label for="thirdParty_creative_name">Name</label>
                    <input type="text" class="form-control" name="thirdParty_creative_name" id="thirdParty_creative_name" placeholder="Name">
                </div>
                <div class="form-group col">
                    <label for="thirdParty_creative_adId">Advertiser</label>
                    <select id="thirdParty_creative_adId" class="form-control" name="thirdParty_creative_adId">
                        <option value="">Select Here</option>
                        <?php foreach ($data['advertiserId'] as $val) : ?>
                            <option value="<?= $val['id']?>"><?= $val['name']?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="snippet">Snippet</label>
                    <textarea id="snippet" class="form-control" rows="2" name="thirdParty_snippet"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
<!--TABLE-->
<div class="container mt-5">
    <table class="table d-none table-bordered table-striped">
        <caption>Created Service List</caption>
        <thead>
        <tr>
            <th>SERVICE</th>
            <th>ID</th>
            <th>NAME</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<!--MODAL-->
<div class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
            </div>
            <div class="modal-body">
                <!--ORDER-->
                <div class="orderField d-none mb-3">
                    <div class="form-group col">
                        <label for="orderName">Name</label>
                        <input type="text" class="form-control" name="orderName" id="orderName" placeholder="Name">
                    </div>
                    <div class="form-group col">
                        <label for="order_advertiserId">Advertiser</label>
                        <select id="order_advertiserId" class="form-control" name="order_advertiserId">
                            <option value="">Select Here</option>
                            <option value="createNewAdvertiser">Add New Advertiser</option>
                            <?php foreach ($data['advertiserId'] as $val) : ?>
                                <option value="<?= $val['id']?>"><?= $val['name']?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <!--ADVERTISER-->
                <div class="advertiserField d-none">
                    <div class="form-row col">
                        <div class="col-9">
                            <input type="text" class="form-control" name="advertiserName" id="advertiserName" placeholder="Advertiser Name">
                        </div>
                        <div class="col">
                            <button class="btn btn-primary createAdvertiser">Create</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info createOrder col d-none">Create</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>