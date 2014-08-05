<?php

Route::controller('home', 'HomeController');
Route::controller('explore/tutorials', 'explore\TutorialController');
Route::controller('explore/publicprojects', 'explore\PublicProjectController');
Route::controller('inventory', 'InventoryController');
Route::controller('projects', 'ProjectController');
Route::controller('profile/setting', 'profile\SettingController');
Route::controller('profile/passwordchange', 'profile\PasswordController');
Route::controller('tutorials', 'TutorialController');
Route::controller('help/home', 'HelpController');
Route::controller('help/query', 'QueryController');
Route::controller('explore/tools', 'explore\ToolController');
Route::controller('home/new', 'OtherHomeController');

Route::controller('profile/appointments', 'profile\AppointmentController');
Route::controller('profile/inventory', 'profile\InventoryController');
Route::controller('profile/usagedata', 'profile\UsageDataController');
Route::controller('profile/projects', 'profile\ProjectController');
Route::controller('profile/questions', 'profile\QuestionController');
Route::controller('profile/listings', 'profile\ListingsController', ['get_index' => 'profile.listings.index']);
Route::controller('profile', 'ProfileController');
Route::controller('/explore/wanted' , "WantedListingsController");

//SecLab
Route::get('seclab/{scanval}','SecLabController@Respond');
Route::get('seclab/{scanval}/{scanval2}','SecLabController@respondException');
Route::get('seclab','SecLabController@Generator');
Route::post('seclab','SecLabController@Generator');

// Apponintment
Route::post('submit', 'profile\AppointmentController@submit');
Route::post('delete', 'profile\AppointmentController@del');
Route::post('update', 'profile\AppointmentController@edit');
Route::post('filter', 'profile\AppointmentController@filter');

//listings controller
Route::resource('fabboard/listings', 'ListingsController');
Route::get('fabboard/listings/{listings}/relist', ['as' => 'fabboard.listings.relist', 'uses' => 'ListingsController@relist']);
Route::post('fabboard/listings/relist/{listings}', ['as' => 'fabboard.listings.storeRelist', 'uses' => 'ListingsController@storeRelist']);
Route::post('fabboard/listings/contact/mail/{listings}', ['as' => 'fabboard.listings.mail', 'uses' => 'ListingsController@mails' ]);

Route::controller('account', 'AuthController');
//Route::controller('fabboard','FabboardController');

Route::group(array('before' => 'auth.admin'), function()
{
Route::controller('admin/inventory', 'admin\InventoryController');
Route::controller('admin/calendar', 'admin\CalendarController');
Route::controller('admin/users', 'admin\UserController');
Route::controller('admin/dashboard', 'admin\DashboardController');
Route::controller('admin/analytics', 'admin\AnalyticsController');
Route::controller('admin', 'admin\HomeController');
});

Route::get('/', function() {
	return Redirect::to('/home');
});

Route::get('/team/abouttransportal', function() {
	return View::make('/site/team/about_transportal_bremen');
});

Route::get('/latestnews/news', function() {
	return View::make('/site/latestnews/news');
});

// Wanted Controller
Route::resource('wanted', 'WantedController');
Route::resource('projectUsers' , 'ProjectUsersController');
//Wanted Listing Controller
Route::controller('/explore/wanted','WantedListingsController');

//question ans answer controller
Route::resource('question', 'QuestionController');
Route::resource('answer', 'AnswerController');

//project controller
Route::get('project/{id}/download', array("uses" => "ProjectController@download"));
Route::resource('project', 'ProjectController');

//projectSession controller
Route::get('projectsession/{id}/download', array("uses" => "ProjectSessionController@download"));
Route::resource('projectsession', 'ProjectSessionController');

// Filters

Route::filter('isadmin?', function()
{

})

?>