var Innocow = Innocow || {};
Innocow.WC = Innocow.WC || {};
Innocow.WC.StockRecords = Innocow.WC.StockRecords || {};

Innocow.WC.StockRecords.Transaction = class Transaction {

    constructor( transactionJSON=undefined ) {

        this.id = undefined;
        this.timestampUnixCreated = undefined;
        this.timestampUnixUpdated = undefined;
        this._change = undefined;        
        this.userId = undefined;
        this.userEmail = undefined;
        this.customerId = undefined;
        this.customerEmail = undefined;
        this.productId = undefined;
        this.productName = undefined;
        this.productSku = undefined;
        this.productUrl = undefined;
        this.orderId = undefined;
        this.orderUrl = undefined;
        this.note = undefined;
        this.isDecrease = undefined;
        this.isManual = undefined;

        this.locale = "en-US";
        this.dateStringOptions = {
            year: "numeric",
            month: "numeric",
            day: "numeric",
            hour: "numeric",
            minute: "numeric"
        }

        if ( transactionJSON ) {
            this.loadFromJSON( transactionJSON );
        }

    }

    get timestampCreated() {
        return this.timestampUnixCreated * 1000;
    }

    set timestampCreated( timestamp ) {
        this.timestampUnixCreated = Math.floor( timestamp / 1000 );
    }

    get timestampUpdated() {
        return this.timestampUnixUpdated * 1000;
    }

    set timestampUpdated( timestamp ) {
        this.timestampUnixUpdated  = Math.floor( timestamp / 1000 );
    }

    get change() {
        return this._change;
    }

    set change( change ) {

        this._change = change;

        if ( change < 0 ) { 
            this.isDecrease = true; 
        } else { 
            this.isDecrease = false; 
        }

    }

    _createHyperlink( innerHTML, url, target="_blank" ) {

        if ( ! url ) {
            return innerHTML;
        }

        let a = document.createElement( "a" );
        a.href = url;
        a.setAttribute( "target", target );
        a.innerHTML = innerHTML;

        return a;

    }

    createProductDisplay() {

        let formattedSku = "";

        if ( this.productSku ) { 
            formattedSku = " (" + this.productSku + ")";
        }

        if ( ! this.productName && ! this.productSku ) {
            return undefined;
        }

        if ( this.productName && ! this.productSku ) {
            return this.productName;
        }

        if ( ! this.productName && this.productSku ) {
            return this.productSku;
        }

        return this.productName + formattedSku

    }

    createHyperlinkProduct( property="productName" ) {

        switch( property ) {

            default:
            case "productName":
                return this._createHyperlink( this.productName, this.productUrl );
                break;

            case "productSku":
                return this._createHyperlink( this.productSku, this.productUrl );
                break;

            case "productDisplay":
                return this._createHyperlink( this.createProductDisplay(), this.productUrl );
                break;

        }

    }

    createHyperlinkOrder() {
        return this._createHyperlink( this.orderId, this.orderUrl );
    }

    createChangeHtml() {

        let container = document.createElement( "span" );
        let elementArrow = document.createElement( "span" );
        let elementValue = document.createElement( "span" );
        let classnameChangeDirection;
        let classnameArrowDirection;
        let changeUnsigned = Math.abs( this.change );

        if ( this.change < 0 ) {

            classnameChangeDirection = "change-down";
            classnameArrowDirection = "dashicons-arrow-down";

        } else if ( this.change > 0 ) {
            
            classnameChangeDirection = "change-up";
            classnameArrowDirection = "dashicons-arrow-up";

        }

        elementArrow.classList.add( "dashicons" );
        elementArrow.classList.add( classnameArrowDirection );

        elementValue.innerHTML = changeUnsigned;

        container.classList.add( classnameChangeDirection );
        container.append( elementArrow );
        container.append( elementValue );
        
        return container;

    }

    selectEmail() {

        if ( ! this.userEmail && ! this.customerEmail ) {
            return undefined;
        }

        if ( this.userEmail && ! this.customerEmail ) {
            return this.userEmail;
        }

        if ( ! this.userEmail && this.customerEmail ) {
            return this.customerEmail;
        }

        if ( this.userEmail && this.customerEmail ) {
            return this.customerEmail;
        }

    }

    toFormSubmission() {
        
        let fields = {};

        if ( this.timestampUnixCreated ) {
            fields["t-datetime-created"] = this.timestampUnixCreated;
        }

        if ( this.timestampUnixUpdated ) {
            fields["t-datetime-updated"] = this.timestampUnixUpdated;
        }

        if ( this.productId ) {
            fields["t-product-id"] = this.productId;
        }

        if ( this.productName ) {
            fields["t-product-name"] = this.productName;
        }

        if ( this.userId ) {
            fields["t-user-id"] = this.userId;
        }

        if ( this.userEmail ) {
            fields["t-user-email"] = this.userEmail;
        }

        if ( this.change ) {

            let direction = ( this.change > 0 ) ? "increase" : "decrease";
            fields["t-stock-amount"] = Math.abs( this.change );
            fields["t-stock-change"] = direction;

        }

        if ( this.orderId ) {
            fields["t-order-id"] = this.orderId;
        }

        if ( this.note ) {
            fields["t-note"] = this.note;
        }

        return fields;

    }

    toFormData( formDataExtraFields ) {

        let formData = new FormData();
        let fields = this.toFormSubmission();

        for ( let fieldName in fields ) {
            formData.append( fieldName, fields[fieldName] );
        }

        if ( formDataExtraFields ) {

            for ( let extraFields of formDataExtraFields.entries() ) {
                formData.append( extraFields[0], extraFields[1] );
            }

        }

        return formData;

    }

    toQueryString() {
        return new URLSearchParams( this.toFormData() ).toString();
    }

    loadFromJSON( j ) {

        if ( j === undefined ) {
            return false;
        }

        this.id = j.id;
        this.timestampUnixCreated = j.timestamp_created;
        this.timestampUnixUpdated = j.timestamp_updated;
        this.userId = j.user_id;
        this.userEmail = j.user_email_admin;
        this.customerEmail = j.user_email_customer;
        this.productId = j.product_id;
        this.productName = j.product_name;
        this.productSku = j.product_sku;
        this.productUrl = j.product_url;
        this.change = j.change;
        this.isDecrease = j.is_decrease;
        this.orderId = j.order_id;
        this.orderUrl = j.order_url;
        this.note = j.note;
        this.isManual = j.is_manual;

    }

    lookupValueByHeader( header ) {

        switch ( header ) {

            case "id":
                return this.id;
                break;
            
            case "date":
                return new Date( this.timestampCreated ).toLocaleString( this.locale, this.dateStringOptions );
                break;

            case "change":
                return this.createChangeHtml();
                break;

            case "product_name_and_sku":
                return this.createHyperlinkProduct( "productDisplay" );
                break;

            case "product_name":
                return this.createHyperlinkProduct( "productName" );
                break;

            case "product_sku":
                return this.createHyperlinkProduct( "productSku" );
                break;

            case "email":
            case "user":
                return this.selectEmail();
                break;

            case "customer_email":
                if ( this.customerEmail ) { return this.customerEmail; }
                break;

            case "order":
                if ( this.orderId ) { return this.createHyperlinkOrder(); }
                break;

            case "note":
                if ( this.note ) { return this.note; }
                break;

        }


    }

}