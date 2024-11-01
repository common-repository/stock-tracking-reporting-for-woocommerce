var Innocow = Innocow || {};
Innocow.WC = Innocow.WC || {};
Innocow.WC.StockRecords = Innocow.WC.StockRecords || {};

Innocow.WC.StockRecords.HtmlTable = class HtmlTable extends Innocow.WC.StockRecords.Html {

    constructor( container ) {

        super( container );

        this._headers = [];
        this._dataset = [];
        this._translations = [];
        this.callbackLookupRowValue = undefined;

        this._defaults = {
            sortDirValues: [ "descending", "ascending" ],
            limitValues: [ 10, 25, 50, 100 ],
        };

        this.dataAttributeNames = {
            translationCode: "data-translation-code",
        };

        this.classNames = {
            forTranslation: "for-translation",
            button: "button",
            table: {
                container: "table",
                containerEmpty: "empty",
                header: "header",
                row: "row",
                cell: "cell",
                value: "value",
                block: "block",
            },
            pagination: {
                button: "button-page",
                first: "first-page",
                prior: "prev-page",
                next: "next-page",
                last: "last-page",
                details: "paging-input",
                detailsCurrent: "current-page",
                detailsTotal: "total-pages",
                
            },
            displayOptions: {
                element: "display-option",
                sortBy: "sortby",
                sortDir: "sortdir",
                limit: "limit",
            }
        };

        this.fieldNames = {
            pagination: {
                page: "page",
            },
            displayOptions: {
                sortBy: "sortby",
                sortDir: "sortdir",
                limit: "limit",
            }
        }

        this.options = {

            locale: "en-US",
            doTranslation: false,
            doTruncate: false,
            textMaxLength: 12,
            textMaxLengthTrailer: "...",
            totalResultsNounSingular: "transaction",
            totalResultsNounPlural: "transactions",
            textEmptyTable: "no_transactions_found",
            
            pagination: {
                textFirstPage: "«",
                textPriorPage: "‹",
                textNextPage: "›",
                textLastPage: "»",
                textDetailsSeparator: "/"
            },

            displayOptions: {
                sortByLabel: "sortby",
                sortBySeparator: "------",
                ignoreSortByColumns: [
                    "product_sku"
                ],
            }

        }

    }

    //
    // Accessors
    //

    get headers() {
        return this._headers;
    }

    set headers( h ) {
        this._headers = typeof( h ) === "object" ? h : [];
    }

    get dataset() {
        return this._dataset;
    }

    set dataset( d ) {
        this._dataset = typeof( d ) === "object" ? d : [];
    }

    get translations() {
        return this._translations;
    }

    set translations( t ) {
        this._translations = typeof( t ) === "object" ? t : [];
    }

    //
    // Formatting methods
    //

    _normaliseClassname( text, replaceWith="-" ) {
        return text.replace( " ", replaceWith );
    }

    _truncate( target ) {

        let container = document.createElement( "span" );

        container.title = target;
        container.alt = target;
        container.innerText = target.substring( 0, this.options.textMaxLength );
        container.innerText += this.options.textMaxLengthTrailer;

        return container;

    }

    _translate( text ) {

        if ( typeof( text ) === "string" ) {
            let textLower = text.toLowerCase();
            return this.translations.hasOwnProperty( textLower ) ? this.translations[textLower] : text;
        }
        
        return text;

    }

    _applyTranslation( code, element ) {

        element.innerHTML = code; // failsafe.

        if ( this.options.doTranslation ) {
            element.innerHTML = this._translate( code );
        } else {

            if ( this.classNames.forTranslation 
            && this.dataAttributeNames.translationCode ) {

                element.classList.add( this.classNames.forTranslation );
                element.setAttribute( this.dataAttributeNames.translationCode, code );

            }

        }

        return element;

    }

    //
    // Table (Responsive) generation.
    //

    append( cnContainers ) {

        if ( cnContainers ) {

            this._setPropertyByClassName( "innerHTML", "", cnContainers, document );
            this._appendChildByClassName( this.create(), cnContainers, document );

        } else {

            if ( this.container && this.container.append ) {
                this.container.append( this.create() );
            }

        }


    }

    create() {

        let table = document.createElement( "div" );

        table.classList.add( this.classNames.table.container );

        if ( this.dataset.length >= 1 ) {

            table.append( this.createHeader() );
            Array.from( this.dataset ).map( data => { table.append( this.createRow( data ) ); } );

        } else {

            let empty = document.createElement( "p" );

            this._applyTranslation( this.options.textEmptyTable, empty );
            table.classList.add( this.classNames.table.containerEmpty );

            table.append( empty );

        }

        return table;

    }

    createHeader() {

        let headerRow = document.createElement( "div" );

        headerRow.classList.add( this.classNames.table.header );

        Array.from( this.headers ).map( header => {
            headerRow.append( this.createHeaderCell( header ) );
        } );        

        return headerRow;

    }

    createHeaderCell( headerInnerHtml ) {

        let header = document.createElement( "div" );        
        let headerValue = document.createElement( "div" );

        header.classList.add( this.classNames.table.header );
        header.append( headerValue );

        headerValue.classList.add( this.classNames.table.value );
        headerValue.classList.add( this._normaliseClassname( headerInnerHtml ) );
        this._applyTranslation( headerInnerHtml, headerValue );

        return headerValue;

    }

    createRow( record ) {

        let row = document.createElement( "div" );

        Array.from( this.headers ).map( header => {
            
            let value;

            if ( typeof( this.callbackLookupRowValue ) === "function" ) {
                value = this.callbackLookupRowValue( header, record ) || "";
            } else {
                value = record[header] || "";
            }

            row.classList.add( this.classNames.table.row );
            row.append( this.createRowCell( value, header ) );

        } );

        return row;

    }

    createRowCell( value, headerText ) {

        let block = document.createElement( "div" );
        let header = document.createElement( "div" );
        let headerValue = document.createElement( "span" );
        let cell = document.createElement( "div" );
        let cellValue = document.createElement( "span" );

        block.classList.add(  this.classNames.table.block );
        block.append( header );
        block.append( cell );

        header.classList.add( this.classNames.table.header );
        header.classList.add( this._normaliseClassname( headerText ) );
        header.append( headerValue );

        headerValue.classList.add( this.classNames.table.value );
        this._applyTranslation( headerText, headerValue );

        cell.classList.add( this.classNames.table.cell );
        cell.classList.add( this._normaliseClassname( headerText ) );
        cell.append( cellValue );
        
        cellValue.classList.add( this.classNames.table.value );
        cellValue.append( value );

        return block;

    }

    //
    // Total results elements.
    //

    appendTotalResults( cnContainers, totalResultsNumber ) {

        let totalResults = this.createTotalResults( totalResultsNumber );

        this._setPropertyByClassName( "innerHTML", "", cnContainers, document );

        Array.from( totalResults.children ).map( child => {
            this._appendChildByClassName( child, cnContainers, document );
        } );

    }

    createTotalResults( value ) {

        let totalResults = document.createElement( "span" );
        let number = document.createElement( "span" );
        let noun = document.createElement( "span" );
        let nounText;

        totalResults.append( number );
        totalResults.append( noun );
        
        number.innerHTML = value + "&nbsp;";

        nounText = value == 1 ? this.options.totalResultsNounSingular : this.options.totalResultsNounPlural;
        this._applyTranslation( nounText, noun );

        noun.classList.add( this.classNames.forTranslation );
        return totalResults;

    }

    //
    // Pagination elements
    //

    syncCurrentPage( currentPage ) {

        this._setPropertyByClassName( 
            "value", 
            currentPage, 
            this.classNames.pagination.detailsCurrent, 
            document 
        );
    }    

    appendPagination( cnContainers, page, pageTotal ) {

        let buttons = this.createPagination( page, pageTotal );

        this._setPropertyByClassName( "innerHTML", "", cnContainers, document );

        Array.from( buttons.children ).map( child => {
            this._appendChildByClassName( child, cnContainers, document );
        } );

    }

    createPagination( page, pageTotal ) {

        let pagination = document.createElement( "span" );
        let linkFirstPage = document.createElement( "a" );
        let linkLastPage = document.createElement( "a" );
        let linkPriorPage = document.createElement( "a" );
        let linkNextPage = document.createElement( "a" );

        let details = document.createElement( "span" );
        let detailsPageCurrent = document.createElement( "input" );
        let detailsPageTotal = document.createElement( "span" );

        pagination.append( linkFirstPage );
        pagination.append( linkPriorPage );
        pagination.append( details );
        pagination.append( linkNextPage );
        pagination.append( linkLastPage );

        linkFirstPage.dataset.page = 1;
        linkFirstPage.innerHTML = this.options.pagination.textFirstPage;
        linkFirstPage.classList.add( this.classNames.button );
        linkFirstPage.classList.add( this.classNames.pagination.button );
        linkFirstPage.classList.add( this.classNames.pagination.first );

        linkPriorPage.dataset.page = Math.max( 1, page - 1 );
        linkPriorPage.innerHTML = this.options.pagination.textPriorPage;
        linkPriorPage.classList.add( this.classNames.button );
        linkPriorPage.classList.add( this.classNames.pagination.button );
        linkPriorPage.classList.add( this.classNames.pagination.prior );

        linkNextPage.dataset.page = page + 1;
        linkNextPage.innerHTML = this.options.pagination.textNextPage;
        linkNextPage.classList.add( this.classNames.button );
        linkNextPage.classList.add( this.classNames.pagination.button );
        linkNextPage.classList.add( this.classNames.pagination.next );

        linkLastPage.dataset.page = pageTotal;
        linkLastPage.innerHTML = this.options.pagination.textLastPage;
        linkLastPage.classList.add( this.classNames.button );
        linkLastPage.classList.add( this.classNames.pagination.button );
        linkLastPage.classList.add( this.classNames.pagination.last );

        details.classList.add( this.classNames.pagination.details );
        details.append( detailsPageCurrent );
        details.append( detailsPageTotal );

        detailsPageCurrent.type = "text";
        detailsPageCurrent.size = "2";
        detailsPageCurrent.name = this.fieldNames.pagination.page;
        detailsPageCurrent.value = page;
        detailsPageCurrent.classList.add( this.classNames.pagination.detailsCurrent );

        detailsPageTotal.classList.add( this.classNames.pagination.detailsTotal );
        detailsPageTotal.innerHTML = this.options.pagination.textDetailsSeparator;
        detailsPageTotal.innerHTML += "&nbsp;" + pageTotal;

        if ( page === 1 ) {

            linkFirstPage.classList.add( "disabled" );
            linkFirstPage.style.pointerEvents = "none";
            linkPriorPage.classList.add( "disabled" );
            linkPriorPage.style.pointerEvents = "none";

        }

        if ( page >= pageTotal ) {

            linkNextPage.classList.add( "disabled" );
            linkNextPage.style.pointerEvents = "none";
            linkLastPage.classList.add( "disabled" );
            linkLastPage.style.pointerEvents = "none";

        }

        return pagination;

    }

    //
    // Display option elements
    //

    syncDisplayOption( name, value ) {
        this._setPropertyByFieldName( "value", value, name, document );
    }

    appendDisplayOptions( cnContainers, sortByValues=[], sortDirValues=[], limitValues=[] ) {

        this._setPropertyByClassName( "innerHTML", "", cnContainers, document );
        let displayOptions = this.createDisplayOptions( sortByValues, sortDirValues, limitValues );

        Array.from( displayOptions.children ).map( child => {
            this._appendChildByClassName( child, cnContainers, document );
        } );

    }

    createDisplayOptions( sortByValues=[], sortDirValues=[], limitValues=[] ) {
        
        let displayOptions = document.createElement( "span" );

        if ( sortByValues ) {

            if ( Array.from( sortByValues ).length === 0 ) {
                displayOptions.append( this.createDisplayOptionSortBy( this.headers ) );
            } else {
                displayOptions.append( this.createDisplayOptionSortBy( sortByValues ) );
            }

        }

        if ( sortDirValues ) {

            if ( Array.from( sortDirValues ).length === 0 ) {
                displayOptions.append( this.createDisplayOptionSortDir( this._defaults.sortDirValues ) );
            } else {
                displayOptions.append( this.createDisplayOptionSortDir( sortDirValues ) );
            }

        }

        if ( limitValues ) {

            if ( Array.from( limitValues ).length === 0 ) {
                displayOptions.append( this.createDisplayOptionLimit( this._defaults.limitValues ) );
            } else {
                displayOptions.append( this.createDisplayOptionLimit( limitValues ) );
            }

        }        

        return displayOptions;

    }

    createDisplayOptionSortBy( values ) {

        let sortBy = document.createElement( "select" );
        let sortByValues = values.slice(0);

        sortBy.name = this.fieldNames.displayOptions.sortBy;
        sortBy.classList.add( this.classNames.displayOptions.sortBy );
        sortBy.classList.add( this.classNames.displayOptions.element );

        sortByValues.unshift(
            this.options.displayOptions.sortByLabel,
            this.options.displayOptions.sortBySeparator
        );

        Array.from( sortByValues ).map( value => {

            if ( this.options.displayOptions.ignoreSortByColumns.includes( value ) ) {
                return;
            }

            let option = document.createElement( "option" );
            option.value = value;
            this._applyTranslation( value, option );
            
            sortBy.append( option );

        } );

        return sortBy;

    }

    createDisplayOptionSortDir( values ) {

        let sortDir = document.createElement( "select" );
        let sortDirValues = values.slice(0);

        sortDir.name = this.fieldNames.displayOptions.sortDir;
        sortDir.classList.add( this.classNames.displayOptions.sortDir );
        sortDir.classList.add( this.classNames.displayOptions.element );

        Array.from( sortDirValues ).map( value => {

            let option = document.createElement( "option" );
            option.value = value;
            this._applyTranslation( value, option );
            
            sortDir.append( option );

        } );

        return sortDir;

    }

    createDisplayOptionLimit( values ) {

        let limit = document.createElement( "select" );
        let limitValues = values.slice(0);

        limit.name = this.fieldNames.displayOptions.limit;
        limit.classList.add( this.classNames.displayOptions.limit );
        limit.classList.add( this.classNames.displayOptions.element );

        Array.from( limitValues ).map( value => {

            let option = document.createElement( "option" );
            option.value = value;
            this._applyTranslation( value, option );
            
            limit.append( option );

        } );

        return limit;
    }

}