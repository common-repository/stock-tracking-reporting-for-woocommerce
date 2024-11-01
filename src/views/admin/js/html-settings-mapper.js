var Innocow = Innocow || {};
Innocow.WC = Innocow.WC || {};
Innocow.WC.StockRecords = Innocow.WC.StockRecords || {};

Innocow.WC.StockRecords.HtmlSettingsMapper = class HtmlSettingsMapper 
extends Innocow.WC.StockRecords.Html {

    constructor( container, arrayOfOrderedColumns ) {

        super( container );

        this.arrayOfOrderedColumns = arrayOfOrderedColumns;

        this.fnDisplayId = "display-id";
        this.fnDisplayEmail = "display-email";
        this.fnDisplayOrder = "display-order";
        this.fnDisplayProductSku = "display-product-sku";
        this.fnDisplayNote = "display-note";

    }

    populateColumns() {

        this.arrayOfOrderedColumns.map( column => {

            switch( column ) {

                case "id":
                    this._setPropertyByFieldName( "checked", true, this.fnDisplayId );
                    break;

                case "email":
                    this._setPropertyByFieldName( "checked", true, this.fnDisplayEmail );
                    break;

                case "order":
                    this._setPropertyByFieldName( "checked", true, this.fnDisplayOrder );
                    break;

                case "product_sku":
                    this._setPropertyByFieldName( "checked", true, this.fnDisplayProductSku );
                    break;

                case "note":
                    this._setPropertyByFieldName( "checked", true, this.fnDisplayNote );
                    break;

            }

        } );

    }

}