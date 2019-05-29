$(document).ready(function () {
   class Services
   {

       constructor() {
           this.modal = $(`.modal`);
           this.advertiserId = null;
           this.orderId = null;
           this.creativeBlockName = null;
       }

        create(form) {
           services.loading();

           let formData = new FormData(form),
                createButton = $(`.create`);
            $.ajax({
                url : `/services/create`,
                type : `post`,
                data : formData,
                dataType : `json`,
                processData : false,
                contentType: false,
                cache : false,
                success : (data) => {
                    services.loading(`stop`);

                    createButton.removeAttr(`disabled`);

                    if(data['error']) {
                        toastr.error(`Fix Errors!`);
                        services.generateError(data['error']);
                        return false;
                    }

                    if(data.length !== 0) services.generate(data);

                },
                error : () => {
                    createButton.removeAttr(`disabled`);
                    services.fail();
                }

            });
        }

        generate(data) {
           let content = $(`#content`),
               table = $(`.table`),
               tbody = $(`tbody`);

           $.each(data, (key, val) => {
               let block = `<tr>
                                <th>${key.toUpperCase()}</th>
                                <td>${val.id}</td>
                                <td>${val.name}</td>
                            </tr>`;
               tbody.append(block);
           });

           content.empty();
           table.removeClass(`d-none`);
           toastr.success(`Successfully created`)
        }

        createAdvertiser () {
           let name = $(`#advertiserName`).val(),
               hasOrderField = $(`.orderField`).hasClass(`d-none`),
               advertiserField =$(`.advertiserField`),
               createButton = $(`.createOrder`);

           advertiserField.addClass(`d-none`);

           if(hasOrderField) this.modal.modal(`hide`);
           else createButton.attr(`disabled`, `disabled`);


           $.ajax({
               url : `/services/createAdvertiser`,
               method : `post`,
               dataType : `json`,
               data : {name : name},
               success : (id) => {
                   services.advertiserId = id;

                   let option = `<option value="${this.advertiserId}" selected>${this.advertiserId}</option>`;
                   $(`#creative_advertiserId`).append(option);

                   if(!hasOrderField) {
                       let select = $(`#order_advertiserId`),
                           option = `<option value="${this.advertiserId}" selected>${this.advertiserId}</option>`;
                       select.append(option);
                       createButton.removeAttr(`disabled`);
                   }
               },
               error : () => {
                   services.fail();
               }
           });
        }

        generateError(error) {
           $.each(error, (key, val) => {
               let field  = Object.keys(val),
                   inputId = field[0],
                   input = $(`#${inputId}`);

               if(input.closest(`.form-group`).find(`.errorBlock`)[0])  return;

               input.after(`<span class="small text-danger errorBlock">${val[inputId]}</span>`)

           })
        }

        createOrder () {
            let name = $(`#orderName`).val(),
                select = $(`#order_advertiserId`);

            this.modal.modal(`hide`);
            this.loading();

            $.ajax({
                url : `/services/createOrder`,
                method : `post`,
                dataType : `json`,
                data : {
                    orderName : name,
                    order_advertiserId : select.val()
                },
                success: (id) => {
                    services.orderId = id;
                    services.loading(`stop`);
                    alert( `Successfully Created`);

                    let option = `<option value="${this.orderId}" selected>${this.orderId}</option>`;
                    $(`#lineItem_orderId`).append(option);
                },
                error : () => services.fail(),
            });

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

       fail() {
           toastr.error(`Fail Request!`);
           services.loading(`stop`);
       }


       events () {
           $('.modal').on('hidden.bs.modal', () => {
               $(`.orderField`).addClass(`d-none`);
               $(`.advertiserField `).addClass(`d-none`);
               $(`.createOrder`).addClass(`d-none`);
               $(`.modal-body input`).val(``);
           });

           $(document).on(`change`, `#creative`, (e) => {
               let creativeFields = $(`.creativeFields`).children(),
                   name = $(e.target).val(),
                   form = $(`.form`);

               if(this.creativeBlockName) this.creativeBlockName.remove();

               if(name === 'image' || name === 'native') {
                   let size = (name === 'image') ? '600x315' : '1x1';

                   $(`option[value=${size}]`).attr(`selected`, `selected`);
               }

               for(let val of creativeFields) {
                   let block = $(name);
                   if(!block.hasClass(`d-none`)) block.addClass(`d-none`)
               }

               this.creativeBlockName = $(`.${name}Creative`).clone();
               this.creativeBlockName.removeClass(`d-none`);

               form.find(`.create`).before(this.creativeBlockName);
           });

           $(document).on(`submit`, `.form`, (e) => {
               e.preventDefault();
               let form = $(e.target);
               form.find(`.create`).attr(`disabled`, `disabled`);
               services.create(form[0]);
           });

           $(`#lineItem_orderId`).change((e) => {
               let elem = $(e.target),
                   val = elem.val(),

                   name = elem.attr(`data-name`),
                   field = $(`.${name}Field`),
                   title = $(`.modal-title`);

               if(val === `newService`) {
                   title.text(`Create ${name}`);
                   field.removeClass(`d-none`);

                   if(name === `order`) $(`.createOrder`).removeClass(`d-none`);

                   this.modal.modal();
               }
           });

           $(`#order_advertiserId`).change((e) => {
               let elem = $(e.target);
               if(elem.val() === 'createNewAdvertiser') $(`.advertiserField`).removeClass(`d-none`);
           });

           $(`.createAdvertiser`).click((e) => {
               services.createAdvertiser();
           });

           $(document).on(`click`, `.createOrder`, (e) => {
               services.createOrder();
           });

           $(`body`).click(() => {
               $(`#loading`).addClass(`d-none`);
           });

           $(document).on(`focus`, `input`, () => {
               $(`.errorBlock`).remove();
           })
       }
   }

   let services = new Services();
   services.events();
});