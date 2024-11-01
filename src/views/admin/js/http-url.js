var Innocow = Innocow || {};
Innocow.WC = Innocow.WC || {};
Innocow.WC.StockRecords = Innocow.WC.StockRecords || {};

Innocow.WC.StockRecords.HttpUrl = class HttpUrl {

    constructor( slug, dashUrl ) {

        this.pageCodeDelete = "delete";
        this.pageCodeManage = "manage";
        this.pageCodeOptions = "options";

        this.slug = slug;
        this.dashUrl = dashUrl;

    }

    createDashUrlPlugin() {
        
        let urlParams = new URLSearchParams( {
            "page": this.slug
        } ).toString();

        return this.dashUrl + "?" + urlParams;

    }

    createDashUrlPluginDelete( id=0 ) {

        let urlParams = new URLSearchParams( {
            "page": this.slug +  "-" + this.pageCodeDelete,
            "id": parseInt( id ),
        } ).toString();

        return this.dashUrl + "?" + urlParams;

    }

    createDashUrlPluginAdd() {

        let urlParams = new URLSearchParams( {
            "page": this.slug + "-" + this.pageCodeManage,
        } ).toString();

        return this.dashUrl + "?" + urlParams;

    }

    createDashUrlPluginEdit( id=0 ) {

        let urlParams = new URLSearchParams( {
            "page": this.slug + "-" + this.pageCodeManage,
            "id": parseInt( id ),
            "mode": "edit"
        } ).toString();

        return this.dashUrl + "?" + urlParams;

    }

    createDashUrlPluginOptions() {

        let urlParams = new URLSearchParams( {
            "page": this.slug + "-" + this.pageCodeOptions,
        } ).toString();

        return this.dashUrl + "?" + urlParams;

    }

    getUrlParameter( key ) {
        return ( new URLSearchParams( window.location.search ) ).get( key );
    }

    createHtmlLink( href, innerHTML, target="_blank" ) {

        let a = document.createElement( "a" );

        a.href = href;
        a.target = target;

        if ( typeof( innerHTML ) === "object" ) { 
            a.append( innerHTML ); 
        } else {
            a.innerHTML = innerHTML;
        }

        return a;

    }

}
