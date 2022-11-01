$(document).ready(function () {
    if ($('.js-address-form').length) {
        loqInitialize();
        prestashop.on('updatedAddressForm', function (params) {
            loqInitialize();
        });
    } else if ($('.adminaddresses #customer_address').length) {
        let loqAddressForm = $('#customer_address');
        let addressInput = loqAddressForm.find('#customer_address_address1');
        let addressMapping = {
            address1: 'Line1',
            address2: 'Line2',
            postcode: 'PostalCode',
            city: 'City',
            country: 'CountryIdPresta',
            state: 'StateId'
        };
        let addressElements = {
            address1: loqAddressForm.find('#customer_address_address1'),
            address2: loqAddressForm.find('#customer_address_address2'),
            postcode: loqAddressForm.find('#customer_address_postcode'),
            city: loqAddressForm.find('#customer_address_city'),
            state: loqAddressForm.find('#customer_address_id_state'),
            country: loqAddressForm.find('#customer_address_id_country'),
            phone: loqAddressForm.find('#customer_address_phone')
        };

        loqCapture(addressInput, addressMapping, addressElements);

        loqAddressForm.parents('form').on('submit', function (e) {
            e.preventDefault();
            loqVerifyAddress(loqAddressForm, addressElements);
        });
    } else if ($('.admincustomers #customer').length) {
        let loqCustomerForm = $('#customer');
        loqCustomerForm.parents('form').on('submit', function (e) {
            e.preventDefault();
            loqVerifyEmail(loqCustomerForm);
        });
    }
});

function loqInitialize() {
    let loqAddressForm = $('.js-address-form');
    let addressInput = loqAddressForm.find('#field-address1');

    let addressMapping = {
        address1: 'Line1',
        address2: 'Line2',
        postcode: 'PostalCode',
        city: 'City',
        country: 'CountryIdPresta',
        state: 'StateId'
    };

    let addressElements = {
        address1: loqAddressForm.find('[name*="address1"]'),
        address2: loqAddressForm.find('[name*="address2"]'),
        postcode: loqAddressForm.find('[name*="postcode"]'),
        city: loqAddressForm.find('[name*="city"]'),
        state: loqAddressForm.find('[name*="id_state"]'),
        country: loqAddressForm.find('[name*="id_country"]')
    };

    loqCapture(addressInput, addressMapping, addressElements);
}

function loqCapture(addressInput, addressMapping, addressElements) {

    // create a DIV element which will contain the addresses
    let addressList = $("<div class='loqate-autocomplete-items'></div>");
    // add custom class to autocomplete container
    addressInput.parent().addClass('loqate-autocomplete-container');
    // append DIV as child to autocomplete container
    $(addressList).insertAfter(addressInput);

    let inputTimer = 0;
    $(addressInput).on('input', function () {
        if ($(addressInput).val()) {
            // cancel any previously-set timer
            if (inputTimer) {
                clearTimeout(inputTimer);
            }

            inputTimer = setTimeout(function () {
                getLoqAddresses(addressInput, addressList);
            }, 500);
        }
    });

    //handle address selection
    $(addressList).on('click', '.loqate-address-item', function (e) {
        e.stopPropagation();
        let addressId = $(this).attr('data-id');
        getCompleteLoqAddress(addressId, addressElements, addressList, addressMapping);
    });

    $('body').on('click', function (e) {
        closeAllLists(this, e);
    });
}

function getLoqAddresses(addressInput, addressList) {
    $(addressList).empty();
    let page = '';
    let countries = [];
    if ($('body').hasClass('adminaddresses')) {
        page = 'adminaddresses';
        $('#customer_address_id_country option').each(function () {
            if (!isNaN(parseInt($(this).val()))) {
                countries.push($(this).val());
            }
        });
    } else {
        page = $('body').attr('id');
        $('#field-id_country option').each(function () {
            if (!isNaN(parseInt($(this).val()))) {
                countries.push($(this).val());
            }
        });
    }
    const params = {
        'text': $(addressInput).val(),
        'countries': JSON.stringify(countries),
        'page': page
    };
    const captureUrl = "/index.php?fc=module&module=loqate&controller=find&" + $.param(params);
    let loader = '';
    if (typeof (is_admin) != 'undefined') {
        loader = img_dir + 'loader.gif';
    } else {
        loader = prestashop.urls.img_ps_url + 'loader.gif';
    }
    $.ajax({
        type: "GET",
        url: captureUrl,
        showLoader: true,
        beforeSend: function () {
            $(addressInput).css({
                'background-image': 'url("' + loader + '")',
                'background-position': 'right',
                'background-repeat': 'no-repeat'
            });
        },
        success: function (response) {
            response = JSON.parse(response);
            if (response['error'] && response['message']) {
                displayLoqFindError(response['message'], addressList);
            } else {
                handleFindApiResponse(response, addressList);
            }
            $(addressInput).css('background-image', 'none');
        },
        error: function (xhr, ajaxOptions, thrownError) {
            displayLoqFindError(thrownError, addressList);
            $(addressInput).css('background-image', 'none');
        }
    });
}

function getCompleteLoqAddress(addressId, addressElements, addressList, addressMapping) {
    $(addressList).empty();
    let page = '';
    if ($('body').hasClass('adminaddresses')) {
        page = 'Admin';
    } else {
        page = $('body').attr('id');
    }
    const params = {
        'address_id': addressId,
        'page': page
    };
    const captureUrl = "/index.php?fc=module&module=loqate&controller=retrieve&" + $.param(params);
    $.ajax({
        type: "GET",
        url: captureUrl,
        showLoader: true,
        success: function (response) {
            response = JSON.parse(response);
            if (response['error'] && response['message']) {
                displayLoqFindError(response['message'], addressList);
            } else {
                handleRetrieveApiResponse(response, addressElements, addressMapping);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            displayLoqFindError(thrownError, addressList);
        }
    });
}

function displayLoqFindError(message, container) {
    if (!message) {
        message = 'Unknown server error';
    }

    const errorElem = $("<div class='loqate-error-item message error alert alert-danger'>" + message + "</div>")
    $(errorElem).appendTo($(container));
}

function handleFindApiResponse(response, addressList) {
    if ($.isArray(response)) {
        response.forEach(function (item) {
            let addressItem = $("<div class='loqate-address-item' data-id='" + item.Id + "'>" + item.Text + "</div>");
            $(addressItem).appendTo($(addressList));
        });
        addressList.show();
    }
}

function handleRetrieveApiResponse(response, addressElements, addressMapping) {
    if ($.isArray(response)) {
        const autofillAddress = response[0];
        $.each(addressMapping, function (key, val) {
            $(addressElements[key]).val(autofillAddress[val]).trigger('change');
        });
        prestashop.on('updatedAddressForm', function (params) {
            if (autofillAddress) {
                addressElements['state'] = $('.js-address-form').find('[name*="id_state"]');
                $.each(addressMapping, function (key, val) {
                    if (key !== 'country') {
                        $(addressElements[key]).val(autofillAddress[val]).trigger('change');
                    }
                });
            }
        });
    }
}

function closeAllLists(elem, event) {
    let suggestions = $('.loqate-autocomplete-items');
    if (event.target !== suggestions && !suggestions.has(event.target).length) {
        suggestions.hide();
    }
    suggestions.each(function () {
        if (!$(this).is(elem)) {
            $(this).empty();
        }
    });
}

function loqVerifyAddress(loqAddressForm, addressElements) {
    let errorText = '';
    let warning = '';
    let submit = true;
    const params = {
        'address1': $(addressElements['address1']).val(),
        'address2': $(addressElements['address2']).val(),
        'postcode': $(addressElements['postcode']).val(),
        'city': $(addressElements['city']).val(),
        'state': $(addressElements['state']).val(),
        'country': $(addressElements['country']).val(),
        'phone': $(addressElements['phone']).val(),
        'page': "Adminaddresses"
    };
    const verifyUrl = module_dir + 'loqate/ajaxAdmin.php?ajax=1&action=verify_address&' + $.param(params);

    $.ajax({
        type: "GET",
        url: verifyUrl,
        showLoader: true,
        success: function (response) {
            if ($('#loq-errors').length) {
                $('#loq-errors').remove();
            }
            if ($('#loq-warning').length) {
                $('#loq-warning').remove();
            }
            if (response['phoneWarning']) {
                warning = '<div class="alert alert-warning" id="loq-warning">';
                for (const warningText of response['warnings']) {
                    warning += `<p>${warningText}</p>`;
                }
                warning += '</div>';
                loqAddressForm.append(warning);
                submit = false;
            }
            if (response['error'] && response['errors'].length) {
                errorText = '<div class="alert alert-danger" id="loq-errors">';
                for (const error of response['errors']) {
                    errorText += `<p>${error}</p>`;
                }
                errorText += '</div>';
                loqAddressForm.append(errorText);
                submit = false;
            }
            if (submit) {
                loqAddressForm.parents('form').off('submit').submit();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            return true;
        }
    });
}

function loqVerifyEmail(loqCustomerForm, loqCustomerEmail) {
    let submit = true;
    let errorText = '';
    let warningText = '';
    const params = {
        'email': $('#customer_email').val(),
        'page': "AdminCustomer"
    };
    const verifyUrl = module_dir + 'loqate/ajaxAdmin.php?ajax=1&action=verify_email&' + $.param(params);

    $.ajax({
        type: "GET",
        url: verifyUrl,
        showLoader: true,
        success: function (response) {
            if ($('#loq-error').length) {
                $('#loq-error').remove();
            }
            if ($('#loq-warning').length) {
                $('#loq-warning').remove();
            }
            if (response['error']) {
                errorText = '<div class="alert alert-danger" id="loq-error">';
                for (const error of response['errors']) {
                    errorText += `<p>${error}</p>`;
                }
                errorText += '</div>';
                loqCustomerForm.append(errorText);
                submit = false;
            }
            if (response['warning']) {
                warningText = '<div class="alert alert-warning" id="loq-warning">';
                for (const warning of response['warnings']) {
                    warningText += `<p>${warning}</p>`;
                }
                warningText += '</div>';
                loqCustomerForm.append(warningText);
                submit = false;
            }
            if (submit) {
                loqCustomerForm.parents('form').off('submit').submit();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            return true;
        }
    });
}