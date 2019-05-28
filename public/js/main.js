$(document).ready(function () {

    class Tools
    {
        constructor () {
            this.orderData = null;
            this.lineItemData = null;
        }


        request (url, funcName) {
            tools.loading();
            $.ajax({
                url : url,
                method : `get`,
                dataType : `json`,
                success : (data) => {
                    tools.loading('stop');

                    if(data.length === 0) {
                        alert(`Empty`);
                        return false;
                    }


                    tools[funcName](data);
                }
            });
        }

        getOrder (data) {
            this.orderData = data;
            let body = $(`.tbody`),
                thead = `<tr>
                            <th>N</th>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>ACTIONS</th>
                        </tr>`;
            $(`.orderThead`).append(thead);
            $(`.tableOrder`).removeClass(`d-none`);
            $(`.more`).removeClass(`d-none`);


            $.each(data, (key, val) => {
                key+=1;
                let block = `<tr data-id="${key}">
                                <th>${key}</th>
                                <td >${val.id}</td>
                                <td>${val.name} <button class="btn btn-link more font-weight-bold text-dark" data-id="${key}">...</button></td>  
                                <td>
                                    <button class="request btn btn-link" data-url="/lineItemService/${val.id}" data-func="getLineItem">Line Items</button> 
                                    <button class="request btn btn-link" data-url="/orderCreativeService/${val.id}" data-func="getOrderCreative">Creatives</button>
                                </td>                           
                            </tr>`;
                body.append(block);
            });
        }

        moreOrderTable (id) {
            let data = this.orderData[id-1],
                modalBody = $(`.content`);


            let block = `<div>
                                <p><span class="font-weight-bold">ID : </span>${data.id}</p>
                                <p><span class="font-weight-bold">NAME : </span>${data.name}</p>
                                <p><span class="font-weight-bold">SET DATE TIME : </span>${data.setDateTime}</p>
                                <p><span class="font-weight-bold">END DATE TIME : </span>${data.endDateTime}</p>
                                <p><span class="font-weight-bold">STATUS : </span>${data.status}</p>
                                <p><span class="font-weight-bold">CURRENCY CODE : </span>${data.currencyCode}</p>
                                <p><span class="font-weight-bold">EXTERNAL ORDER ID : </span>${data.externalOrderId}</p>
                                <p><span class="font-weight-bold">ADVERTISER ID : </span>${data.advertiserId}</p>
                                <p><span class="font-weight-bold">AGENCY ID : </span>${data.agencyId}</p>
                                <p><span class="font-weight-bold">CREATOR ID : </span>${data.creatorId}</p>
                                <p><span class="font-weight-bold">TRAFFICKER ID : </span>${data.traffickerId}</p>
                            </div>`;
            modalBody.append(block);

            $(`.modal`).modal();

        }

        getLineItem (data) {
            this.lineItemData = data;

            let table = $(`.lineItemTable`),
                thead = $(`.lineItemThead`),
                tbody = $(`.lineItemTbody`);

            table.removeClass(`d-none`);
            thead.empty();
            tbody.empty();

            let head = `<tr>
                            <th>N</th>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>ACTION</th>
                        </tr>`;
            thead.append(head);

            $.each(data, (key, val)  => {
                key+=1;
                let body = `<tr>
                                <th>${key}</th>
                                <td>${val.id}</td>
                                <td>${val.name}<button class="btn btn-link moreLineItem font-weight-bold text-dark" data-id="${key}">...</button></td>
                                <td>
                                    <button class="request btn btn-link" data-url="/creativeService/${val.id}" data-func="getCreative">Creative</button>
                                </td>
                            </tr>`;
                tbody.append(body);
            });

            table.removeClass(`d-none`);
        }

        moreLineItem (id) {
            let data = this.lineItemData[id-1],
                modalTitle = $(`.modal-title`),
                modalBody = $(`.content`);

            let block = `<div>
                                <p><span class="font-weight-bold">ID : </span>${data.id}</p>
                                <p><span class="font-weight-bold">NAME : </span>${data.name}</p>
                                <p><span class="font-weight-bold">SET DATE TIME : </span>${data.startDateTime}</p>
                                <p><span class="font-weight-bold">END DATE TIME : </span>${data.endDateTime}</p>
                                <p><span class="font-weight-bold">CREATION DATE TIME : </span>${data.creationDateTime}</p>
                                <p><span class="font-weight-bold">LINE ITEM TYPE : </span>${data.lineItemType}</p>
                                <p><span class="font-weight-bold">COST TYPE : </span>${data.costType}</p>
                                <p><span class="font-weight-bold">DISCOUNT TYPE: </span>${data.discountType}</p>
                                <p><span class="font-weight-bold">CREATIVE PLACEHOLDER SIZE : </span>${data.creativePlaceholderSize.join(", ")}</p>
                                <p><span class="font-weight-bold">ENVIRONMENT TYPE : </span>${data.environmentType}</p>
                                <p><span class="font-weight-bold">COMPANION DELIVERY OPTION : </span>${data.companionDeliveryOption}</p>
                                <p><span class="font-weight-bold">STATUS : </span>${data.status}</p>
                                <p><span class="font-weight-bold">RESERVATION STATUS : </span>${data.reservationStatus}</p>                             
                            </div>`;
            modalBody.append(block);
            modalTitle.text(`LINE ITEM INFO`);
            $(`.modal`).modal();
        }

        getCreative (data) {
            let modal = $(`.modal`),
                modalBody = $(`.content`),
                modalTitle = $(`.modal-title`),
                table = $(`.modal-table`),
                thead = $(`.modalThead`),
                tbody = $(`.modalTbody`),

                head = `<tr>
                            <th>N</th>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>SIZE</th>
                            <th>ADVERTISER ID</th>
                        </tr>`;


            thead.append(head);

            $.each(data, (key, val) => {
                key+=1;
                let body = `<tr>
                                <th>${key}</th>
                                <td>${val.id}</td>
                                <td>${val.name}</td>
                                <td>${val.size}</td>
                                <td>${val.advertiserId}</td>
                            </tr>`;

                tbody.append(body);
            });

            table.removeClass(`d-none`);

            modalTitle.text(`Creative Service`);

            modal.modal();
        }

        getOrderCreative (data) {
            let table = $(`.creativeTable`),
                thead = $(`.creativeThead`),
                tbody = $(`.creativeTbody`),
                caption = $(`.creativeTable caption`);

            thead.empty();
            tbody.empty();

            let head = `<tr>
                            <th>N</th>
                            <th>ID</th>
                            <th>NAME</th>
                            <th>SIZE</th>
                            <th>ADVERTISER ID</th>
                        </tr>`;

            thead.append(head);

            $.each(data, (key, val) => {
                key+=1;
                let body = `<tr>
                                <th>${key}</th>
                                <td>${val.id}</td>
                                <td>${val.name}</td>
                                <td>${val.size}</td>
                                <td>${val.advertiserId}</td>
                            </tr>`;

                tbody.append(body);
            });

            caption.text(`List Of Creative`);
            table.removeClass(`d-none`);
        }

        loading(action = '') {
            let loadingBlock = $(`#loading`),
                body = $(`#content`),
                opacityBody = 1,
                func = 'addClass';

            if(action === 'start' || !action) {
                func = 'removeClass';
                opacityBody = 0.6;
            }

            body.css(`opacity`, opacityBody);

            loadingBlock[func](`d-none`);
        }

        empty () {
            $(`.default`).removeAttr(`selected`).attr(`selected`, true);
            $(`thead`).empty();
            $(`.tbody`).empty();

            (`.table`).addClass(`d-none`);
        }

        events () {
            $('.modal').on('hidden.bs.modal', () => {
                $(`.modal-table .modalThead`).empty();
                $(`.modal-table .modalTbody`).empty();
                $(`.content`).empty();
            });

            $(`#tool`).change((e) => {
                let elem = $(e.target),
                    funcName = elem.val(),
                    url = elem.find(`option[value=${funcName}]`).attr(`data-url`);

                if(funcName === `default`) {
                    tools.empty();
                    return;
                }

                if(url && funcName) tools.request(url, funcName);
            });

            $(document).on(`click`, `.more`, (e) => {
                let elem = $(e.target),
                    id = elem.attr(`data-id`);
                tools.moreOrderTable(id);
            });

            $(document).on(`click`, `.empty`, (e) => {
                tools.empty();
            });

            $(document).on(`click`, `.request`, (e) => {
                let elem = $(e.target),
                    funcName = elem.attr(`data-func`),
                    url = elem.attr(`data-url`);

                if(url && funcName) tools.request(url, funcName);
            });

            $(document).on(`click`, `.moreLineItem`, (e) => {
                let elem = $(e.target),
                    id = elem.attr(`data-id`);
                tools.moreLineItem(id);
            });
        }
    }

    let tools = new Tools();
    tools.events();
});