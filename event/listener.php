<?php
/**
*
* Annual Star [English]
*
* @package language
* @copyright (c) 2020 Richard McGirr
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace rmcgirr83\annualstar\event;

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	private $reg_years = 0;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;


	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_cache_user_data'	=> 'viewtopic_cache_user_data',
			'core.viewtopic_cache_guest_data'	=> 'viewtopic_cache_guest_data',
			'core.viewtopic_modify_post_row'	=> 'viewtopic_modify_post_row',
			'core.memberlist_view_profile'		=> 'memberlist_view_profile',
		);
	}

	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param \phpbb\user	$user		User object
	*/
	public function __construct(\phpbb\template\template $template, \phpbb\user $user)
	{
		$this->template = $template;
		$this->user = $user;
	}

	public function viewtopic_cache_guest_data($event)
	{
		$array = $event['user_cache_data'];
		$star = '';
		$array['annual_stars'] = $star;
		$event['user_cache_data'] = $array;
	}

	public function viewtopic_cache_user_data($event)
	{
		$array = $event['user_cache_data'];
		$array['annual_star'] = $this->annual_star($event['row']['user_regdate']);
		$event['user_cache_data'] = $array;
	}

	public function viewtopic_modify_post_row($event)
	{
		$event['post_row'] = array_merge($event['post_row'], array('ANNUAL_STAR' => $event['user_poster_data']['annual_star']));
		/* remove joined date from viewtopic?  If so uncomment the next two lines */
		/*$joined = $event['user_poster_data']['joined'];
		$event['post_row'] = !empty($event['user_poster_data']['annual_star']) ? array_merge($event['post_row'], array('POSTER_JOINED' => '')) : array_merge($event['post_row'], array('POSTER_JOINED' => $joined));*/
	}

	public function memberlist_view_profile($event)
	{
		$star = $this->annual_star($event['member']['user_regdate']);

		$this->template->assign_vars(array(
			'ANNUAL_STAR'	=> $star,
		));
	}

	private function annual_star($reg_date)
	{
		$this->user->add_lang_ext('rmcgirr83/annualstar', 'annualstar');
		$star = '';
		if ($reg_years = (int) ((time() - (int) $reg_date) / 31536000))
		{
			$this->reg_years = $reg_years;
			$reg_output = sprintf($this->user->lang['YEAR_OF_MEMBERSHIP'], $reg_years);

			if ($reg_years > 1)
			{
				$reg_output = sprintf($this->user->lang['YEARS_OF_MEMBERSHIP'], $reg_years);
			}
			$star = $this->generate_star($reg_output);
		}
		return $star;
	}

	private function generate_star($reg_output)
	{
		/* change below to whatever colors you want */
		$star_color = 'style="color:#AFEEEE;"';
		$year_color = 'style="color:black;"';
		if ($this->reg_years >= 20)
		{
			$star_color = 'style="color:#27408B;"';
			$year_color = 'style="color:white;"';
		}
		else if ($this->reg_years >= 10)
		{
			$star_color = 'style="color:#3A5FCD;"';
			$year_color = 'style="color:white;"';
		}
		else if ($this->reg_years >= 5)
		{
			$star_color = 'style="color:#4876FF;"';
			$year_color = 'style="color:white;"';
		}
		return '<span class="fa-stack fa-lg annual_star" ' . $star_color . ' title="'  . $reg_output .  '">
					<i class="fa fa-star fa-stack-2x"></i>
					<i class="fa fa-stack-1x" ' . $year_color . '>' . $this->reg_years .'</i>
				</span>';
	}
}
