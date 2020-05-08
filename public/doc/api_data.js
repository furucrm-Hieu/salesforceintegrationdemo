define({ "api": [
  {
    "type": "post",
    "url": "https://login.salesforce.com/services/oauth2/token",
    "title": "Get token Salesforce",
    "name": "GetToken",
    "group": "1.OAuth2",
    "version": "0.0.1",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "client_id",
            "description": "<p>The connected app’s consumer key</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "client_secret",
            "description": "<p>The connected app’s consumer secret</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "allowedValues": [
              "\"authorization_code\""
            ],
            "optional": false,
            "field": "grant_type",
            "description": "<p>The type of validation that the connected app can provide to prove it's a safe visitor. For the web server flow, the value must be authorization_code.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "redirect_uri",
            "description": "<p>The URL where users are redirected after a successful authentication. The redirect URI must match one of the values in the connected app’s Callback URL field</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "code",
            "description": "<p>A temporary authorization code received from the authorization server. The connected app uses this code in exchange for an access token</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Example-Request:",
          "content": "https://login.salesforce.com/services/oauth2/token?client_id=3MVG9n_HvETGhr3AmxuR5EwQh8ovnQtPOC8cbZgIALJIstZsYe8fzdDmCbAJjhTmpzF37YFt.3EC.R1VNn1RH&client_secret=F7BCB164BF27401D56FE2D19FCA20312D398A79188F9BA42EF3B78F98BE06A8B&redirect_uri=http://localhost:8000/oauth2/callback&code=aPrx4sgoM2Nd1zWeFVlOWveD0HhYmiDiLmlLnXEBgX01tpVOQMWVSUuafFPHu3kCSjzk4CUTZg==&grant_type=authorization_code",
          "type": "x-www-form-urlencoded"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "access_token",
            "description": "<p>OAuth token that a connected app uses to request access to a protected resource on behalf of the client application. Additional permissions in the form of scopes can accompany the access token.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "refresh_token",
            "description": "<p>Token that a connected app uses to obtain new access tokens (sessions). This value is a secret.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "signature",
            "description": "<p>Base64-encoded HMAC-SHA256 signature signed with the client_secret. The signature can include the concatenated ID and issued_at value, which you can use to verify that the identity URL hasn’t changed since the server sent it.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "scope",
            "description": "<p>A space-separated list of scopes values.Scopes further define the type of protected resources that the client can access. You assign scopes to a connected app when you build it, and they are included with the OAuth tokens during the authorization flow.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "instance_url",
            "description": "<p>A URL indicating the instance of the user’s org.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": "<p>Identity URL that can be used to identify the user and to query for more information about the user.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token_type",
            "description": "<p>A Bearer token type, which is used for all responses that include an access token.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "issued_at",
            "description": "<p>Time stamp of when the signature was created.</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Example-Success",
          "content": "{\n    \"access_token\": \"00D2w000003yx07!ARAAQPA1GOAK6HbT9tvanJTNB1T7ntobhp_bb.PWHnjFtuPy4v7MF5aRbUoZQACrpawr0J615u2ft_85x.CsMkn69VP3qkiI\"\n    \"refresh_token\": \"5Aep861ZBQbtA4s3JUvLPxi.ria2BFrEU4KlP3aY43kyhG47DsmCItTGeaberMQh3Z14LXWl5mIvz0NImlEAb_Q\"\n    \"signature\": \"S49Ohp3XaxR352arJJr/4jmpc+PYefhCAlIAmbFUdh0=\"\n    \"scope\": \"refresh_token api\"\n    \"instance_url\": \"https://eap-prototype-dev-ed.my.salesforce.com\"\n    \"id\": \"https://login.salesforce.com/id/00D2w000003yx07EAA/0052w000002J9asAAC\"\n    \"token_type\": \"Bearer\"\n    \"issued_at\": \"1588840929064\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "1.OAuth2"
  },
  {
    "type": "get",
    "url": "https://login.salesforce.com/services/oauth2/authorize",
    "title": "User Authenticates and Authorizes Access",
    "name": "OauthSalesforce",
    "group": "1.OAuth2",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"application/x-www-form-urlencoded\""
            ],
            "optional": false,
            "field": "ContentType",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "client_id",
            "description": "<p>The connected app’s consumer key.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "redirect_uri",
            "description": "<p>The URL where users are redirected after a successful authentication. The redirect URI must match one of the values in the   connected app’s Callback URL field.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "allowedValues": [
              "\"code\""
            ],
            "optional": false,
            "field": "response_type",
            "description": "<p>The OAuth 2.0 grant type that the connected app requests.</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Example-Request:",
          "content": "https://login.salesforce.com/services/oauth2/authorize?client_id=3MVG9n_HvETGhr3AmxuR5EwQh8ovnQtPOC8cbZgIALJIstZsYe8fzdDmCbAJjhTmpzF37YFt.3EC.R1VNn1RH&redirect_uri=http://localhost:8000/oauth2/callback&response_type=code",
          "type": "x-www-form-urlencoded"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "Code",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Example-Success",
          "content": "{\n    \"code\": \"aPrx4sgoM2Nd1zWeFVlOWveD0HhYmiDiLmlLnXEBgX01tpVOQMWVSUuafFPHu3kCSjzk4CUTZg==\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "1.OAuth2"
  },
  {
    "type": "post",
    "url": "https://login.salesforce.com/services/oauth2/token",
    "title": "Refresh Token Salesforce",
    "name": "RefreshToken",
    "group": "1.OAuth2",
    "version": "0.0.1",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "client_id",
            "description": "<p>The connected app’s consumer key.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "client_secret",
            "description": "<p>The connected app’s consumer secret.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "allowedValues": [
              "\"refresh_token\""
            ],
            "optional": false,
            "field": "grant_type",
            "description": "<p>The OAuth 2.0 grant type that the connected app requests. The value must be refresh_token for this flow.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "redirect_uri",
            "description": "<p>The URL where users are redirected after a successful authentication. The redirect URI must match one of the values in the connected app’s Callback URL field.</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "refresh_token",
            "description": "<p>Token that a connected app uses to obtain new access tokens (sessions).</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Example-Request:",
          "content": "https://login.salesforce.com/services/oauth2/token?client_id=3MVG9n_HvETGhr3AmxuR5EwQh8ovnQtPOC8cbZgIALJIstZsYe8fzdDmCbAJjhTmpzF37YFt.3EC.R1VNn1RH&client_secret=F7BCB164BF27401D56FE2D19FCA20312D398A79188F9BA42EF3B78F98BE06A8B&redirect_uri=http://localhost:8000/oauth2/callback&refresh_token= 5Aep861ZBQbtA4s3JUvLPxi.ria2BFrEU4KlP3aY43kyhG47Dt7DRz3qoYaJ1BvoUuTKXBcbnnogJJiKzN7hNtI&grant_type=refresh_token",
          "type": "x-www-form-urlencoded"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "access_token",
            "description": "<p>OAuth token that a connected app uses to request access to a protected resource on behalf of the client application. Additional permissions in the form of scopes can accompany the access token.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "signature",
            "description": "<p>Base64-encoded HMAC-SHA256 signature signed with the client_secret. The signature can include the concatenated ID and issued_at value, which you can use to verify that the identity URL hasn’t changed since the server sent it.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "A",
            "description": "<p>space-separated list of scopes values.Scopes further define the type of protected resources that the client can access. You assign scopes to a connected app when you build it, and they are included with the OAuth tokens during the authorization flow.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "instance_url",
            "description": "<p>A URL indicating the instance of the user’s org.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": "<p>Identity URL that can be used to identify the user and to query for more information about the user.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "token_type",
            "description": "<p>A Bearer token type, which is used for all responses that include an access token.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "issued_at",
            "description": "<p>Time stamp of when the signature was created.</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Example-Success",
          "content": "{\n    \"access_token\": \"00D2w000003yx07!ARAAQJxRl8X0NEwRz3.loGLP_iHVd_SBtYlHw__r3KAOsBRIw3havvuUUWu2ieVj0YTP8h5c13TFF5Da.YqDLLslM4RmRXUD\"\n    \"signature\": \"oUb5WGnLtbeGgVHJA0/RKJNbKiBRCZe6fIdy0NqOKyQ=\"\n    \"scope\": \"refresh_token api\"\n    \"instance_url\": \"https://eap-prototype-dev-ed.my.salesforce.com\"\n    \"id\": \"https://login.salesforce.com/id/00D2w000003yx07EAA/0052w000002J9asAAC\"\n    \"token_type\": \"Bearer\"\n    \"issued_at\": \"1588841618262\"\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "1.OAuth2"
  },
  {
    "type": "delete",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Proposal__c/a082w000000ZiI6AAK",
    "title": "Delete Budget",
    "name": "DeleteBudget",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 204 Success\n{}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "type": "delete",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Proposal__c/a082w000000ZiI6AAK",
    "title": "Delete Proposal",
    "name": "DeleteProposal",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 204 Success\n{}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "type": "delete",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Proposal__c/a0A2w000001ZzAIEA0",
    "title": "Delete Proposal Budget",
    "name": "DeleteProposalBudget",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 204 Success\n{}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "type": "post",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Budget__c",
    "title": "Insert Budget",
    "name": "InsertBudget",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          },
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"application/json\""
            ],
            "optional": false,
            "field": "Content-Type",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "size": "80",
            "optional": false,
            "field": "Name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "size": "4",
            "optional": false,
            "field": "Year__c",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n    \"Name\": \"Example Budget\",\n    \"Year__c\": \"2020\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "Array",
            "optional": false,
            "field": "errors",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "success",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 200 Success\n{\n    \"id\": \"a092w000002BUitAAG\",\n    \"success\": true,\n    \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "type": "post",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Proposal__c",
    "title": "Insert Proposal",
    "name": "InsertProposal",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          },
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"application/json\""
            ],
            "optional": false,
            "field": "Content-Type",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "size": "80",
            "optional": false,
            "field": "Name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Proposed_At__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "size": "4",
            "optional": false,
            "field": "Year__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "size": "255",
            "optional": false,
            "field": "Details__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Approved_At__c",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n    \"Name\": \"Example Proposal\",\n    \"Proposed_At__c\": \"2020-05-7T12:00:00\",\n    \"Approved_At__c\": \"2020-05-8T12:00:00\",\n    \"Year__c\": \"2020\",\n    \"Details__c\": \"Exapmle detail\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "Array",
            "optional": false,
            "field": "errors",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "success",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 200 Success\n{\n    \"id\": \"a082w000000ZiI6AAK\",\n    \"success\": true,\n    \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "type": "post",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Proposal__c",
    "title": "Insert Proposal Budget",
    "name": "InsertProposalBudget",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          },
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"application/json\""
            ],
            "optional": false,
            "field": "Content-Type",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "size": "80",
            "optional": false,
            "field": "Name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Budget__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Proposal__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "Double",
            "optional": false,
            "field": "Amount__c",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n    \"Name\": \"Example Proposal Budget\",\n    \"Budget__c\": \"a092w000002BUitAAG\",\n    \"Proposal__c\": \"a082w000000ZiI6AAK\",\n    \"Amount__c\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "id",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "Array",
            "optional": false,
            "field": "errors",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "Boolean",
            "optional": false,
            "field": "success",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 200 Success\n{\n    \"id\": \"a082w000000ZiI6AAK\",\n    \"success\": true,\n    \"errors\": []\n}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "type": "patch",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Proposal__c/a092w000002BUitAAG",
    "title": "Update Budget",
    "name": "UpdateBudget",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          },
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"application/json\""
            ],
            "optional": false,
            "field": "Content-Type",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "size": "80",
            "optional": false,
            "field": "Name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "size": "4",
            "optional": false,
            "field": "Year__c",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n    \"Name\": \"Update Budget\",\n    \"Year\": \"2020\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 204 Success\n{}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "type": "patch",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Proposal__c/a082w000000ZiI6AAK",
    "title": "Update Proposal",
    "name": "UpdateProposal",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          },
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"application/json\""
            ],
            "optional": false,
            "field": "Content-Type",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "size": "80",
            "optional": false,
            "field": "Name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Proposed_At__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "size": "4",
            "optional": false,
            "field": "Year__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "size": "255",
            "optional": false,
            "field": "Details__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Approved_At__c",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n    \"Name\": \"Update Example Proposal\",\n    \"Proposed_At__c\": \"2020-05-9T12:00:00\",\n    \"Approved_At__c\": \"2020-05-10T12:00:00\",\n    \"Year__c\": \"2020\",\n    \"Details__c\": \"Update Exapmle detail\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 204 Success\n{}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "type": "patch",
    "url": "https://login.salesforce.com/services/data/v48.0/sobjects/Proposal__c/a0A2w000001ZzAIEA0",
    "title": "Update Proposal Budget",
    "name": "UpdateProposalBudget",
    "group": "2.Api_Call",
    "version": "0.0.1",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"Bearer Token\""
            ],
            "optional": false,
            "field": "Authorization",
            "description": "<p>Set oauth token that a connected app uses to request access to a protected resource on behalf of the client application</p>"
          },
          {
            "group": "Header",
            "type": "String",
            "allowedValues": [
              "\"application/json\""
            ],
            "optional": false,
            "field": "Content-Type",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "size": "80",
            "optional": false,
            "field": "Name",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Budget__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "Proposal__c",
            "description": ""
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "Amount__c",
            "description": ""
          }
        ]
      },
      "examples": [
        {
          "title": "Request-Example:",
          "content": "{\n    \"Amount__c\": \"200\"\n}",
          "type": "json"
        }
      ]
    },
    "success": {
      "examples": [
        {
          "title": "Success-Reponse",
          "content": "HTTP/1.1 204 Success\n{}",
          "type": "json"
        }
      ]
    },
    "filename": "./ApiController.php",
    "groupTitle": "2.Api_Call"
  },
  {
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "optional": false,
            "field": "varname1",
            "description": "<p>No type.</p>"
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "varname2",
            "description": "<p>With type.</p>"
          }
        ]
      }
    },
    "type": "",
    "url": "",
    "version": "0.0.0",
    "filename": "./doc/main.js",
    "group": "E:\\Programing\\docAPI\\salesforceintegrationdemo\\app\\http\\controllers\\doc\\main.js",
    "groupTitle": "E:\\Programing\\docAPI\\salesforceintegrationdemo\\app\\http\\controllers\\doc\\main.js",
    "name": ""
  }
] });
