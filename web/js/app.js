angular.module("MovieasterApp", ["movieaster.components", "movieaster.services"])
	.controller('MovieasterCtrl', function($scope, movieService, wishlistService, folderService, $http, $location) {
		$scope.groups = [
			{ title: "Newest", icon: "./web/img/favicon.ico", view: "newest", mode: "table", sort: {column: 'i', descending: false}, filter: ""},
			{ title: "Watched", icon: "./web/img/watched.png", view: "watched",  mode: "table", sort: {column: 'i', descending: false}, filter: ""}, 
			{ title: "Favorites", icon: "./web/img/favorites.png", view: "favorites",  mode: "table", sort: {column: 'i', descending: false}, filter: ""}, 
			{ title: "Archive", icon: "./web/img/archive.png", view: "archived",  mode: "table", sort: {column: 'i', descending: false}, filter: ""}, 
			{ title: "Wishlist", icon: "./web/img/wishlist.png", view: "wishlist",  mode: "table", sort: {column: 'i', descending: false}}
		];
		
		$scope.movies = {
			"newest": [],
			"watched": [],
			"favorites": [],
			"archived": [],
			"wishlist": []
		};

		$scope.sorterCssClass = function(column, group) {
			if(group.sort.column == column) {
				return (group.sort.descending) ? 'ui-icon-triangle-1-s' : 'ui-icon-triangle-1-n';
			}
			return 'ui-icon-carat-2-n-s';
		};
		
		$scope.movieFilter = function(idx, group) {
			var filter = group.filter;
			if(filter == null || filter == "") {
				return "";
			}
			var splited = filter.split(",");
			if(splited.length > idx) {
				return splited[idx].trim();
			}
			return "";	
		};

		$scope.addMovieFilter = function(text) {
			var newFilter = text.trim();
			var oldFilters = $scope.groups[0].filter.split(", ");
			var cleanFilters = [];
	        angular.forEach(oldFilters, function(dirtyFilter) {
		        var cleanFilter = dirtyFilter.trim();
		    	if(cleanFilter != "" && cleanFilter != ",") {
			    	cleanFilters.push(cleanFilter);
		    	}
	        });
			if(cleanFilters.indexOf(newFilter) == -1) {
				cleanFilters.push(newFilter);
			} else {
				var idx = cleanFilters.indexOf(newFilter);
				cleanFilters.splice(idx, 1);
			}
			angular.forEach($scope.groups, function(group) {
				group.filter = cleanFilters.join(", ");
	        });
		};		
				
		$scope.reload = function() {
			$scope.progress.start("Load all Movies...");
			movieService.movies($scope.movies, function (result) {
				$scope.movies = result;	
				$scope.progress.start("Load Wishlist...");
				wishlistService.movies(function (result) {
					$scope.movies["wishlist"] = result;	
					$scope.progress.done("Initialized.");
				}, function() {
					$scope.progress.error("Wishlist backend communication error.");
				});
			}, function() {
				$scope.progress.error("Movies backend communication error.");
			});
		};
		
		$scope.progress = {
			start: function(msg) {
	            if(!msg) {
		            msg = "Start...";
	            }
				$scope.msg.info(msg);
				$scope.progressActive = true;
			},
            error: function(msg) {
	            if(!msg) {
		            msg = "Error";
	            }
	            $scope.msg.warning(msg);
	            $scope.progressActive = false;
			},
            done: function(msg) {
	            if(!msg) {
		            msg = "Done.";
	            }
	            $scope.msg.info(msg);
	            $scope.progressActive = false;
			}
		};

		$scope.msg = {
			info: function(msg) {
				$scope.msgInfo = true;
				$scope.msgWarning = false;
				$scope.progressMsg = msg;
			},
            warning: function(msg) {
	            $scope.msgInfo = false;
				$scope.msgWarning = true;
	            $scope.progressMsg = msg;
			},
            error: function(msg) {
	            $scope.progressMsg = msg;
			}
		};

		$scope.status = {
			watched: function(id) {
				return $scope.status.inView(id, "watched");
			},
			favorites: function(id) {
				return $scope.status.inView(id, "favorites");
			},
			archived: function(id) {
				return $scope.status.inView(id, "archived");
			},
			inView: function(id, view) {
				var located = false;
		        angular.forEach($scope.movies[view], function(movie) {
			    	if(movie.i == id) {
				    	located = true;
			    	}
		        });
				return located;
			}
		}
		
		$scope.move = {
			watched: function(movie, view) {
				$scope.move.fadeOut(movie, view);
				var mode = "add to";
				if($scope.status.watched(movie.i)) {
					mode = "remove from";
				}
				var info = '"' + movie.t + '" ' + mode + ' Watched.';
				$scope.progress.start(info + '..');
				movieService.watched(movie.i, $scope.movies, function (data) {
					$scope.progress.done('Done: ' + info);
				}, function(error) {
					$scope.progress.error('error: Move ' + info);
				});
			},
			favorites: function(movie, view) {
				$scope.move.fadeOut(movie, view);
				var mode = "add to";
				if($scope.status.favorites(movie.i)) {
					mode = "remove from";
				}
				var info = '"' + movie.t + '" ' + mode + ' Favorites.';
				$scope.progress.start(info + '..');
				movieService.favorites(movie.i, $scope.movies, function (data) {
					$scope.progress.done('Done: ' + info);
				}, function(error) {
					$scope.progress.error('error: Move ' + info);
				});
			},	
			archived: function(movie, view) {
				$scope.move.fadeOut(movie, view);
				var mode = "add to";
				if($scope.status.archived(movie.i)) {
					mode = "remove from";
				}
				var info = '"' + movie.t + '" ' + mode + ' Archived.';
				$scope.progress.start(info + '..');
				movieService.archived(movie.i, $scope.movies, function (data) {
					$scope.progress.done('Done: ' + info);
				}, function(error) {
					$scope.progress.error('error: Move ' + info);
				});
			},	
			fadeOut: function(movie, view) {
				var idx = 0;
		        angular.forEach($scope.movies[view], function(m) {
			    	if(movie.i == m.i) {
				    	// TODO: for now, just remove it...
			    		$scope.movies[view].splice(idx, 1);
			    	}
			    	idx++;  
		        });
			}
		};
		
		$scope.wishlist = {
			service : wishlistService,
			item : {
				label: '',
				value: ''
			},
			addItem : function(data){
				$scope.progress.start("Add new Wishlist item...");
				wishlistService.create(data.value, function (result) {
					$scope.wishlist.item.label = '';
					$scope.movies["wishlist"] = result;	
					$scope.progress.done("Wishlist updated.");
				}, function() {
					$scope.progress.error("TMDb service communication error.");
				});
			},
			removeItem : function(movie){
				$scope.progress.start('remove "' + movie.t + '" from Wishlist...');
				$scope.move.fadeOut(movie, "wishlist");
				wishlistService.remove(movie.i, function (result) {
					$scope.movies["wishlist"] = result;	
					$scope.progress.done("Wishlist updated.");
				}, function() {
					$scope.progress.error("Wishlist backend communication error.");
				});
			}			
		};
				
		$scope.movieDetails = null;
		
		$scope.showDetails = function(movie) {
			$scope.movieDetails = movie;
		};
		
		$scope.refreshFolders = function() {
			$scope.progress.start("refresh Folders...");
			folderService.refresh(function(msg) {
				$scope.msg.info(msg);	
			}, function() {
				$scope.progress.start("reload all Movies...");
				movieService.movies($scope.movies, function (result) {
					$scope.movies = result;	
					$scope.progress.done("Initialized.");
				}, function() {
					$scope.progress.error("Movies backend communication error.");
				});
			}, function(msg) {
				$scope.progress.error("error: " + msg);
			});
		};
		
		$scope.clearLocalStorage = function() {
			$scope.progress.start("clear localStorage...");
			localStorage.clear();
			$scope.progress.done("localStorage cleared.");
		};
		
		$scope.settings = function() {
			window.location = PATH + "/path/";
		};
	})
	.filter('capitalize', function() {
		return function(input, scope) {
			return input.capitalize();
		}
	});

		
String.prototype.capitalize = function() {
	return this.charAt(0).toUpperCase() + this.slice(1);
}
	
angular.element(document).ready(function () {
	//TODO?
});