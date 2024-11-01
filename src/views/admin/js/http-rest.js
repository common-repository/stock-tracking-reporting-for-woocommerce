var Innocow = Innocow || {};
Innocow.WC = Innocow.WC || {};
Innocow.WC.StockRecords = Innocow.WC.StockRecords || {};

Innocow.WC.StockRecords.HttpRest = class HttpRest {

    constructor( urlBase, urlPathPlugin, nonce ) {

        this.endpointTransactions = "/transactions";
        this.endpointResourcesProducts = "/resources/products";
        this.endpointResourcesTranslations = "/resources/translations";
        this.endpointResourcesUsers = "/resources/users";
        this.endpointSettings = "/settings";

        if ( urlBase === undefined ) {
            throw new Error( "Undefined first parameter: restBaseUrl" ); 
        }

        if ( urlPathPlugin === undefined ) {
            throw new Error( "Undefined second parameter: urlPathPlugin" ); 
        }

        this.urlBase = urlBase;
        this.urlPathPlugin = urlPathPlugin;
        this.nonce = nonce || undefined;        

        // In case pathPlugin has a leading "/", remove it.
        if ( this.urlBase.substr( -1 ) === "/" && this.urlPathPlugin.charAt( 0 ) === "/" ) {
            this.urlPathPlugin = this.urlPathPlugin.substr( 1 );
        }

        this.debug = false;

    }

    getRestUrlTransactions() {
        return this.urlBase + this.urlPathPlugin + this.endpointTransactions;
    }

    getRestUrlResourcesProducts() {
        return this.urlBase + this.urlPathPlugin + this.endpointResourcesProducts;
    }

    getRestUrlResourcesTranslations() {
        return this.urlBase + this.urlPathPlugin + this.endpointResourcesTranslations;
    }

    getRestUrlResourcesUsers() {
        return this.urlBase + this.urlPathPlugin + this.endpointResourcesUsers;
    }

    getRestUrlSettings() {
        return this.urlBase + this.urlPathPlugin + this.endpointSettings;
    }

    _applyParameters( url, parameters ) {

        if ( url.includes( "/wp-json/" ) && parameters ) {
            url += "?" + parameters;
        }

        if ( url.includes( "/index.php?rest_route=/") && parameters ) {
            url += "&" + parameters;
        }
        
        return url;

    }

    async _fetch( url, parameters=undefined, method="GET", requestHeaders={} ) {

        let requestOptions = { 
            method: method,
            headers: requestHeaders,
        }

        if ( this.nonce ) { 
            requestHeaders["X-WP-Nonce"] = this.nonce;
            requestOptions["credentials"] = "include";
        }

        switch ( method ) {

            case "POST":
                if ( parameters ) {
                    requestHeaders["Content-Type"] = "application/x-www-form-urlencoded";
                    requestOptions["body"] = parameters;
                }
                break;

            case "PUT":
                if ( parameters ) {
                    requestHeaders["Content-Type"] = "application/x-www-form-urlencoded";
                    requestOptions["body"] = parameters;
                }
                break;

            case "GET":
            case "DELETE":
            default:
                if ( parameters ) {
                    url = this._applyParameters( url, parameters );
                }
                break;

        }

        if ( this.debug ) {
            console.debug( url );
            console.debug( requestOptions );
        }

        return fetch(
            url,
            requestOptions
        );

    }

    async getResourcesTranslations() {

        return this._fetch( this.getRestUrlResourcesTranslations() );

    }

    async getResourcesProducts( search ) {

        let endpoint = this.getRestUrlResourcesProducts();
        let parameters = new URLSearchParams( { "s": search } ).toString();

        return this._fetch( endpoint, parameters );

    }

    async putSettings( formFields ) {
        
        let form;

        if ( formFields.constructor.name === "FormData" ) {
            form = formFields;
        } else {
            form = new FormData( formFields );            
        }

        let endpoint = this.getRestUrlSettings();
        let parameters = new URLSearchParams( form ).toString();

        return this._fetch( endpoint, parameters, "PUT" );

    }

    async searchTransactions( formFields ) {

        let form;

        if ( formFields.constructor.name === "FormData" ) {
            form = formFields;
        } else {
            form = new FormData( formFields );            
        }

        let parameters = new URLSearchParams( form ).toString();

        return this._fetch( this.getRestUrlTransactions(), parameters, "GET" );

    }

    async postTransaction( formFields ) {

        let form;

        if ( formFields.constructor.name === "FormData" ) {
            form = formFields;
        } else {
            form = new FormData( formFields );            
        }

        let parameters = new URLSearchParams( form ).toString();

        return this._fetch( this.getRestUrlTransactions(), parameters, "POST" );

    }

    async getTransaction( id ) {

        let endpoint = this.getRestUrlTransactions() + "/" + id;

        return this._fetch( endpoint );

    }

    async putTransaction( id, formFields ) {

        let form;

        if ( formFields.constructor.name === "FormData" ) {
            form = formFields;
        } else {
            form = new FormData( formFields );
        }

        let endpoint = this.getRestUrlTransactions() + "/" + id;
        let parameters = new URLSearchParams( form ).toString();

        return this._fetch( endpoint, parameters, "PUT" );

    }

    async deleteTransaction( id, formFields ) {

        let form;

        if ( formFields.constructor.name === "FormData" ) {
            form = formFields;
        } else {
            form = new FormData( formFields );            
        }

        let endpoint = this.getRestUrlTransactions() + "/" + id;
        let parameters = new URLSearchParams( form ).toString();

        return this._fetch( endpoint, parameters, "DELETE" );

    }  

    async translateElements( cnToTranslate, daAtCode, container=document ) {

        return this.getResourcesTranslations()
        .then( response => this.parseNetworkResponse( response ) )
        .then( responseJSON => {

            if ( typeof( responseJSON ) !== "object" ) {
                throw new Error( responseJSON );
            }

            Array.from( container.getElementsByClassName( cnToTranslate ) ).map( element => {

                let translationCode = element.getAttribute( daAtCode );

                if ( responseJSON.hasOwnProperty( translationCode ) ) {
                    element.innerHTML = responseJSON[translationCode];
                } else {
                    element.innerHTML = translationCode;
                }

            } );

        } )
        .catch( error => {

            Array.from( container.getElementsByClassName( cnToTranslate ) ).map( element => { 
                element.innerHTML = element.getAttribute( daAtCode );
            } );
            console.error( error );

        } );

    }

    async parseNetworkResponse( promiseResponse, callbackOnClientError, returnType="json" ) {

        if ( ! promiseResponse.ok ) {

            return promiseResponse.text().then( text => {

                let errorMessage;

                try { 
                    errorMessage = JSON.parse( text ).message;
                } catch( error ) {
                    errorMessage = text;
                }

                if ( 400 <= promiseResponse.status && promiseResponse.status <= 499 ) {

                    if ( typeof( callbackOnClientError ) === "function" ) {
                        callbackOnClientError( errorMessage );
                    } else {
                        console.error( errorMessage );
                    }

                } else {
                    throw new Error( errorMessage );
                }

            } );

        }

        switch( returnType ) {

            case "text":
                return promiseResponse.clone().text().catch( () => promiseResponse.text() );
                break;

            case "blob":
                return promiseResponse.clone().blob().catch( () => promiseResponse.text() );
                break;

            case "json":
            default:
                // Wonky JS: if json() fails because its just a string, catch and return the text.
                return promiseResponse.clone().json().catch( () => promiseResponse.text() );
                break;

        }


    }    

}