<?php



	class WSConstants

	{

        // transactional constants

		const STATE_DB_ERROR = 1;

		const STATE_CREATE_FAILED = 2;

		const STATE_CREATE_SUCCESS = 0;

		const STATE_INCORRECT_URL = 3;

		const STATE_INCORRECT_PARAMS = 4;

		const STATE_UNKNOW_FAILURE = -1;

		const STATE_WRONG_APIKEY = 5;

		const STATE_NO_APIKEY = 6;

		const STATE_SUCCESS = 7;

        const STATE_NOT_FOUND = 8;

        const STATE_DELETE_SUCCESS = 9;

        const STATE_DELETE_FAILED = 10;

        const STATE_UPDATE_SUCCESS = 11;

        const STATE_UPDATE_FAILED = 12;

        

        // operation constants

        const OPER_SIGNUP = 100;

        const OPER_LOGIN = 101;
		
        const OPER_REFRESH_SESSION = 102;
                
        const OPER_UPDATE_USER = 103;

        const OPER_SHOW_FRIENDS = 200;

        const OPER_SEARCH_PLP = 201;

        const OPER_ADD_FRIEND = 202;

        const OPER_REM_FRIEND = 203;
		
		const OPER_SYNC_FRIENDS = 204;
		
		const OPER_IS_FRIEND = 205;
		
		const OPER_SHOW_RND_PRODUCTS = 300;
		
		const OPER_LIKE_PRODUCT = 301;
		
		const OPER_REJECT_PRODUCT = 302;
		
		const OPER_FILTER_PRODUCTS = 303;
		
		const OPER_SHOW_LIKED_PRODUCTS = 304;
		
		const OPER_SHOW_REJECTED_PRODS = 305;
		
		const OPER_SHOW_PRODS_PARAM = 306;
                
                const OPER_ADD_PROD = 307;
                
                const OPER_REJ_PROD = 308;
                
                const OPER_SHOW_PRODS_BY_STATE = 309;
                
                const OPER_SYNC_PRODUCTS = 310;
		
		
		
		// messages format constants
		
		const FIELD_CONTROL = "control";
		
		const FIELD_DATA = "data";
		
		const FIELD_OPERATION = "operation";
		
		const FIELD_RESPONSE = "response";
		
		const FIELD_STATE = "state";
		
		const FIELD_MESSAGE = "message";

	}

?>