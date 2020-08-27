<?php
/**
*
* @package Annual Star
* @copyright (c) 2020 Richard McGirr
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace rmcgirr83\annualstar\event;

use phpbb\language\language;
use phpbb\template\template;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var language */
	protected $language;

	/** @var template */
	protected $template;

	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param language	$language	Language object
	* @param template	$template	Template object
	*/
	public function __construct(language $language, template $template)
	{
		$this->language = $language;
		$this->template = $template;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_extensions_run_action_after'	=>	'acp_extensions_run_action_after',
			'core.viewtopic_cache_guest_data'		=> 'viewtopic_cache_guest_data',
			'core.viewtopic_cache_user_data'		=> 'viewtopic_cache_user_data',
			'core.viewtopic_modify_post_row'	=> 'viewtopic_modify_post_row',
			'core.memberlist_view_profile'		=> 'memberlist_view_profile',
		);
	}

	/* Display additional metdate in extension details
	*
	* @param $event			event object
	* @param return null
	* @access public
	*/
	public function acp_extensions_run_action_after($event)
	{
		if ($event['ext_name'] == 'rmcgirr83/annualstar' && $event['action'] == 'details')
		{
			$this->language->add_lang('annualstar', $event['ext_name']);
			$this->template->assign_var('S_BUY_ME_A_BEER_ANNUALSTAR', true);
		}
	}

	/**
	* Update viewtopic guest data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_guest_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_regdate'] = '';
		$event['user_cache_data'] = $array;
	}

	/**
	* Update viewtopic user data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_user_data($event)
	{
		$array = $event['user_cache_data'];
		$array['user_regdate'] = $event['row']['user_regdate'];
		$event['user_cache_data'] = $array;
	}

	/**
	* Modify the post row in viewtopic
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_modify_post_row($event)
	{
		$reg_date = $event['user_poster_data']['user_regdate'];
		$poster_id = $event['poster_id'];
		$user_type = $event['user_poster_data']['user_type'];

		if ($user_type != USER_IGNORE && !empty($reg_date))
		{
			$star_css = $this->annual_star($poster_id, $reg_date);

			if (!empty($star_css))
			{
				$event['post_row'] = array_merge($event['post_row'], [
					'STAR_COLOR' => $star_css[$poster_id]['star_color'],
					'YEAR_COLOR' => $star_css[$poster_id]['year_color'],
					'REG_OUTPUT' => $star_css[$poster_id]['reg_output'],
					'REG_YEARS' => $star_css[$poster_id]['reg_years']
				]);
			}
		}
	}

	/**
	* Add star to member profile
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function memberlist_view_profile($event)
	{
		$reg_date = $event['member']['user_regdate'];
		$user_id = $event['member']['user_id'];

		$star_css = $this->annual_star($user_id, $reg_date);

		if (!empty($star_css))
		{
			$this->template->assign_vars([
				'STAR_COLOR' => $star_css[$user_id]['star_color'],
				'YEAR_COLOR' => $star_css[$user_id]['year_color'],
				'REG_OUTPUT' => $star_css[$user_id]['reg_output'],
				'REG_YEARS' => $star_css[$user_id]['reg_years']
			]);
		}
	}

	/**
	* Display a users annual star
	*
	* @param 	int 	$reg_date 	The users registration date
	* @return	string
	* @access	public
	*/
	private function annual_star($user_id, $reg_date)
	{
		$this->language->add_lang('annualstar', 'rmcgirr83/annualstar');
		$star = '';
		if ($reg_years = (int) ((time() - (int) $reg_date) / 31536000))
		{
			if ($reg_years >= 1)
			{
				$reg_output = $this->language->lang('YEAR_OF_MEMBERSHIP', $reg_years);

				//this gets the css for the star
				$star = $this->generate_star($user_id, $reg_output, $reg_years);
			}
		}
		return $star;
	}

	/**
	* Generate display of the star
	*
	* @param	int 	$reg_output 	The users registration date
	* @return	string
	* @access	public
	*/
	private function generate_star($user_id, $reg_output = '', $reg_years = 0)
	{
		/*
		* change below to whatever colors you want
		* star_color	the interior color of the star
		* year_color	the color of the number that displays within the star
		* lighter star_color will require darker year_color and vice versa
		*/

		// year 20 and over
		if ($reg_years >= 20)
		{
			$css_array = [
				'star_color' => 'style="color:#27408B;cursor: pointer;"',
				'year_color' => 'style="color:white;"'];
		}
		// year 10 through 20
		else if ($reg_years >= 10)
		{
			$css_array = [
				'star_color' => 'style="color:#3A5FCD;cursor: pointer;"',
				'year_color' => 'style="color:white;"'];
		}
		// year 5 through 10
		else if ($reg_years >= 5)
		{
			$css_array = [
				'star_color' => 'style="color:#4876FF;cursor: pointer;"',
				'year_color' => 'style="color:white;"'];
		}
		//year 1 to 5
		else if ($reg_years >= 1)
		{
			$css_array = [
				'star_color' => 'style="color:#0076B1;cursor: pointer;"',
				'year_color' => 'style="color:white;"'];
		}

		$css_array[$user_id] = array_merge($css_array, ['reg_output' => $reg_output, 'reg_years' => $reg_years]);

		return $css_array;
	}
}
