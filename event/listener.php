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
			'core.viewtopic_cache_user_data'	=> 'viewtopic_cache_user_data',
			'core.viewtopic_cache_guest_data'	=> 'viewtopic_cache_guest_data',
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
			$this->template->assign_vars([
				'L_BUY_ME_A_BEER_EXPLAIN'	=> $this->language->lang('BUY ME A BEER_EXPLAIN', '<a href="' . $this->language->lang('BUY_ME_A_BEER_URL') . '" target="_blank" rel=”noreferrer noopener”>', '</a>'),
				'S_BUY_ME_A_BEER_ANNUALSTAR' => true,
			]);
		}
	}

	/**
	* Add entry to guest cache data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_guest_data($event)
	{
		$array = $event['user_cache_data'];
		$star = '';
		$array['annual_star'] = $star;
		$event['user_cache_data'] = $array;
	}

	/**
	* Add entry to user cache data
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function viewtopic_cache_user_data($event)
	{
		$array = $event['user_cache_data'];
		$array['annual_star'] = $this->annual_star($event['row']['user_regdate']);
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
		$event['post_row'] = array_merge($event['post_row'], array('ANNUAL_STAR' => $event['user_poster_data']['annual_star']));
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
		$star = $this->annual_star($event['member']['user_regdate']);

		$this->template->assign_vars(array(
			'ANNUAL_STAR'	=> $star,
		));
	}

	/**
	* Display a users annual star
	*
	* @param 	int 	$reg_date 	The users registration date
	* @return	string
	* @access	public
	*/
	private function annual_star($reg_date)
	{
		$this->language->add_lang('annualstar', 'rmcgirr83/annualstar');
		$star = '';
		if ($reg_years = (int) ((time() - (int) $reg_date) / 31536000))
		{
			$reg_output = $this->language->lang('YEAR_OF_MEMBERSHIP', $reg_years);

			//this generates the actual display of the star
			$star = $this->generate_star($reg_output, $reg_years);
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
	private function generate_star($reg_output = '', $reg_years = 0)
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
			$star_color = 'style="color:#27408B;cursor: pointer;"';
			$year_color = 'style="color:white;"';
		}
		// year 10 through 20
		else if ($reg_years >= 10)
		{
			$star_color = 'style="color:#3A5FCD;cursor: pointer;"';
			$year_color = 'style="color:white;"';
		}
		// year 5 through 10
		else if ($reg_years >= 5)
		{
			$star_color = 'style="color:#4876FF;cursor: pointer;"';
			$year_color = 'style="color:white;"';
		}
		//year 1 to 5
		else if ($reg_years >= 1)
		{
			$star_color = 'style="color:#0076B1;cursor: pointer;"';
			$year_color = 'style="color:white;"';
		}
		return '<span class="fa-stack fa-lg annual_star" ' . $star_color . ' title="'  . $reg_output .  '">
					<i class="fa fa-star fa-stack-2x"></i>
					<i class="fa fa-stack-1x" ' . $year_color . '>' . $reg_years .'</i>
				</span>';
	}
}
