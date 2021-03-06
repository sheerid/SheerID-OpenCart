<?php 
/* 
  #file: admin/controller/total/sheer_id.php
  #name: Fixed Payment Type Charge Free Version
  #version: v1.0 free
  #tested: opencart Version 1.5.1.3
  
  modulo creato da fabiom7 - fabiome77@hotmail.it
  copyright fabiom7 2012
*/
?>
<?php 
class ControllerTotalSheerID extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('total/sheer_id');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		$this->load->model('tool/sheer_id');
		
		if ($this->model_tool_sheer_id->allowEmail()) {
			$emailNotifier = $this->model_tool_sheer_id->getEmailNotifier();
		}
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			
			// don't store email settings locally
			$email_settings = $this->request->post['sheer_id_email_config'];
			unset($this->request->post['sheer_id_email_config']);
			
			$this->model_setting_setting->editSetting('sheer_id', $this->request->post);
			
			// update any cached settings, in case they have changed
			foreach ($this->request->post as $k => $v) {
				$this->config->set($k, $v);
			}
		
			if ($this->model_tool_sheer_id->allowEmail()) {
				if ($emailNotifier) {
					$emailNotifier = $this->model_tool_sheer_id->updateEmailNotifier($emailNotifier->id, $email_settings);
				} else {
					$emailNotifier = $this->model_tool_sheer_id->addEmailNotifier($email_settings);
				}
			} else {
				$this->model_tool_sheer_id->removeEmailNotifier();
			}
		
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$affiliationTypes = $this->model_tool_sheer_id->getAffiliationTypes();
		
		$email_settings = $this->model_tool_sheer_id->getEmailDefaults();
		
		if ($emailNotifier) {
			$my_settings = new ArrayObject($emailNotifier->config);
			foreach ($email_settings as $k => $v) {
				if (isset($my_settings[$k])) {
					$email_settings[$k] = $my_settings[$k];
				}
			}
		}
		
		$this->data["email_settings"] = $email_settings;
		
		foreach ($affiliationTypes as $a) {
			try {
				$this->data["label_$a"] = $this->language->get("label_$a");
			} catch (Exception $e) {
				$this->data["label_$a"] = $a;
			}
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_mode_production'] = $this->language->get('text_mode_production');
		$this->data['text_mode_sandbox'] = $this->language->get('text_mode_sandbox');
		
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['entry_access_token'] = $this->language->get('entry_access_token');
		$this->data['entry_mode'] = $this->language->get('entry_mode');
		$this->data['entry_custom_fields'] = $this->language->get('entry_custom_fields');
		$this->data['entry_coupons'] = $this->language->get('entry_coupons');
		$this->data['entry_allow_uploads'] = $this->language->get('entry_allow_uploads');
		$this->data['entry_send_email'] = $this->language->get('entry_send_email');
					
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

   		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),      		
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_total'),
			'href'      => $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('total/sheer_id', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('total/sheer_id', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');

		$keys = array('sheer_id_status', 'sheer_id_sort_order', 'sheer_id_access_token', 'sheer_id_base_url', 'sheer_id_allow_uploads', 'sheer_id_send_email', 'sheer_id_fields');

		foreach ($keys as $k) {
			if (isset($this->request->post[$k])) {
				$this->data[$k] = $this->request->post[$k];
			} else {
				$this->data[$k] = $this->config->get($k);
			}
		}

		$this->load->model('sale/coupon');
		
		$query = array(
			'sort'  => 'name',
			'order' => 'ASC',
			'start' => 1,
			'limit' => 1000
		);
		
		$this->data['coupons'] = $this->model_sale_coupon->getCoupons($query);
		
		$this->data['affiliation_types'] = $affiliationTypes;
		
		$maps = array();
		$settings = $this->model_setting_setting->getSetting('sheer_id');
		
		foreach ($settings as $k => $v) {
			$matches = array();
			if (strpos($k, "affiliation_types") === 0) {
				$maps[$k] = $v;
			}
		}
		
		$this->data['affiliation_type_mappings'] = $maps;
		$this->data['offers'] = isset($settings["offer"]) ? $settings["offer"] : array();
		
		$this->template = 'total/sheer_id.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'total/sheer_id')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>