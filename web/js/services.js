angular.module('movieaster.services', [])
    .factory('movieService', function ($http) {
        var reloadMovies = function (idsMap, oldMovies, successCallback, errorCallback) {
	        var storagePrefix = "movie_";
			var newIds = [];
			var allIds = [];
			allIds = allIds.concat(idsMap["newest"], idsMap["watched"], idsMap["favorites"], idsMap["archived"]);
			angular.forEach(allIds, function(id) {
				if(localStorage.getItem(storagePrefix + id) == null) {
					newIds.push(id);
				}
			});
			var loadMovieFromSessionStorage = function(ids) {
				var movies = [];
		        angular.forEach(ids, function(id) {
					movies.push(JSON.parse(localStorage.getItem(storagePrefix + id)));
				});
				return movies;
			}
			var createResult = function(oldMovies, idsMap) {
				oldMovies["newest"] = loadMovieFromSessionStorage(idsMap["newest"]);
				oldMovies["watched"] = loadMovieFromSessionStorage(idsMap["watched"]);
				oldMovies["favorites"] = loadMovieFromSessionStorage(idsMap["favorites"]);
				oldMovies["archived"] = loadMovieFromSessionStorage(idsMap["archived"]);
				return oldMovies;
			};
			if(newIds.length == 0) {
				successCallback(createResult(oldMovies, idsMap));
			} else {
				$http.jsonp(PATH + '/movie/infos?ids=' + newIds.join(',') + '&callback=JSON_CALLBACK').success(function(newMovies) {
					angular.forEach(newMovies, function(newMovie) {
						localStorage.setItem(storagePrefix + newMovie.i, JSON.stringify(newMovie));
					});
					successCallback(createResult(oldMovies, idsMap));
				}).error(errorCallback);
			}		        
        };
        return {        
            movies: function (oldMovies, successCallback, errorCallback) {
				$http.jsonp(PATH + '/movie/ids?callback=JSON_CALLBACK').success(function (idsMap) {
					reloadMovies(idsMap, oldMovies, successCallback, errorCallback);
				}).error(errorCallback);
            },
            watched: function (id, oldMovies, successCallback, errorCallback) {
                $http.jsonp(PATH + '/movie/' + id + '/switch/watched?callback=JSON_CALLBACK').success(function (idsMap) {
					reloadMovies(idsMap, oldMovies, successCallback, errorCallback);
				}).error(errorCallback);
            },
            favorites: function (id, oldMovies, successCallback, errorCallback) {
                $http.jsonp(PATH + '/movie/' + id + '/switch/favorites?callback=JSON_CALLBACK').success(function (idsMap) {
					reloadMovies(idsMap, oldMovies, successCallback, errorCallback);
				}).error(errorCallback);
            },
            archived: function (id, oldMovies, successCallback, errorCallback) {
                $http.jsonp(PATH + '/movie/' + id + '/switch/archived?callback=JSON_CALLBACK').success(function (idsMap) {
					reloadMovies(idsMap, oldMovies, successCallback, errorCallback);
				}).error(errorCallback);
            }
        };
    })
    .factory('folderService', function($http) {
		var notFound = new Array();
		var numNew = null;
		var foundCounter = 0;
		
		var downloadImage = function(id, image, errorCallback) {
			$http.jsonp(PATH + '/folder/' + id + '/download/image/' + image + '?callback=JSON_CALLBACK')
				.error(function() { errorCallback(imag + "image for folder " + id); } );
		};
		var downloadAllImages = function(id, sucessCallback, errorCallback) {
			$http.jsonp(PATH + '/folder/' + id + '/download/image/thumb?callback=JSON_CALLBACK').success(function(thumbData) {
				downloadImage(id, "folder", errorCallback);
				downloadImage(id, "backdrop1", errorCallback);
				downloadImage(id, "backdrop2", errorCallback);
				downloadImage(id, "backdrop3", errorCallback);
				sucessCallback("thumb image downloaded.");
			}).error(function() { errorCallback("thumb image for folder " + folderData["i"]); } );
		};
		var downloadMetaInfos = function(id, sucessCallback, errorCallback) {
			$http.jsonp(PATH + '/folder/' + id + '/download/meta?callback=JSON_CALLBACK').success(function(movieData) {
				if(movieData["f"] == 1) {
					sucessCallback("TMDb: " + movieData["n"]);
				} else {
					sucessCallback(movieData["e"] + ": " + movieData["n"]);
					notFound.push(movieData["n"]);		
				}		
			}).error(errorCallback);	
		};
		var recursiveProcessTmdbRequests = function(infoMsgCallback, doneCallback, errorCallback) {
			$http.jsonp(PATH + '/folder/todo/next?callback=JSON_CALLBACK').success(function(folderData) {
				if(folderData["i"] != -1) {
					foundCounter++;
					infoMsgCallback("Folder " + foundCounter + "/" + numNew + ": " + folderData["n"] + "...");
					downloadMetaInfos(folderData["i"], function(msg) {
							infoMsgCallback(msg);
							downloadAllImages(folderData["i"], function() {
								recursiveProcessTmdbRequests(infoMsgCallback, doneCallback, errorCallback);
							}, errorCallback);
						}, errorCallback);
				} else {
					doneCallback();	
				}
			}).error(errorCallback);	
		};
        return {
	        refresh: function(infoMsgCallback, doneCallback, errorMsgCallback) {
				notFound = new Array();
				foundCounter = 0;
				infoMsgCallback("refresh filesystem...");
				$http.jsonp(PATH + '/folder/refresh?callback=JSON_CALLBACK').success(function(data) {
					infoMsgCallback("New: " + data["n"]  + " / Deleted: " + data["d"] + " / Old: " + data["o"]);
					numNew = data["n"];
			        recursiveProcessTmdbRequests(infoMsgCallback, doneCallback, errorMsgCallback);
				}).error(function() {
					errorMsgCallback("refresh folder.");	
				});
            },
            cancel: function(successCallback) {
	            //folderWorker.terminate();
            }
        };
    })
	.factory('wishlistService', function($http) {
		var reloadWishlist = function (allIds, successCallback, errorCallback) {
            var storagePrefix = "wishlist_";
			var newIds = [];
			angular.forEach(allIds, function(id) {
				if(localStorage.getItem(storagePrefix + id) == null) {
					newIds.push(id);
				}
			});
			var loadMoviesFromWishlistSessionStorage = function(ids) {
				var movies = [];
		        angular.forEach(ids, function(id) {
					movies.push(JSON.parse(localStorage.getItem(storagePrefix + id)));
				});
				return movies;
			}
			if(newIds.length == 0) {
				successCallback(loadMoviesFromWishlistSessionStorage(allIds));
			} else {
				$http.jsonp(PATH + '/wishlist/infos?ids=' + newIds.join(',') + '&callback=JSON_CALLBACK').success(function(newMovies) {
					angular.forEach(newMovies, function(newMovie) {
						localStorage.setItem(storagePrefix + newMovie.i, JSON.stringify(newMovie));
					});
					successCallback(loadMoviesFromWishlistSessionStorage(allIds));
				}).error(errorCallback);
			}
        };
        		
	    return {
            movies: function (successCallback, errorCallback) {
				$http.jsonp(PATH + '/wishlist/ids?callback=JSON_CALLBACK').success(function (allIds) {
					reloadWishlist(allIds, successCallback, errorCallback);
				}).error(errorCallback);
            },
	        AutoComplete: function(request, response) {
		        // based on: http://jsfiddle.net/ZguhP/
	            var retArray, dataToPost;
	            dataToPost = {
	                term: request.term,
	                callback: 'JSON_CALLBACK'
	            };
	            config = {
	                method: 'JSONP',
	                url: PATH + '/wishlist/tmdb_query',
	                params: dataToPost
	            };
	            $http.jsonp(config.url, config).
	            success(function(data, status, headers, config) {
	                retArray = data.map(function(item) {
	                    return {
	                        label: item.label,
	                        value: item.id
	                    }
	                });
	                response(retArray);
	            }).
	            error(function(data, status, headers, config) {
	                response([]);
	            });
	        },
	        create: function(tmdbId, successCallback, errorCallback) {
				$http.jsonp(PATH + '/wishlist/' + tmdbId + '/create?callback=JSON_CALLBACK').success(function (allIds) {
			        reloadWishlist(allIds, successCallback, errorCallback);
				}).error(errorCallback);
	        },
	        remove: function(id, successCallback, errorCallback) {
				$http.jsonp(PATH + '/wishlist/' + id + '/remove?callback=JSON_CALLBACK').success(function (allIds) {
					localStorage.removeItem("wishlist_" + id);
			        reloadWishlist(allIds, successCallback, errorCallback);
				}).error(errorCallback);
	        }
	    }
	});