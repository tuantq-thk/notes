<?php


class Controller_User_Newentry_Import extends Controller_User_Common_V2_Base
{

	const EDIT    = "edit";
	const CONFIRM = "confirm";
	const planPay = -1;

	public function before()
	{
		$this->appear_pc_search         = false;
		$this->appear_pc_global_navi    = false;
		$this->appear_sp_side_menu      = false;

		parent::before();

		$this->pankuzu->add('データをインポートする');
		// メタ情報
		$this->meta = array_merge(
			$this->meta,
			(new Service_User_Metatagmanager())
				->setType(Model_Metatag::NEWENTRY)
				->generate()
		);
		$this->meta['noindex'] 	= 'noindex';
		$this->meta['nofollow'] = 'nofollow';
		$this->meta['canonical'] = Uri::base() . 'import_newentry/';
		// 画面基本情報の設定
		$this->css .= Asset::css("user/pc/v2/newentry/entry.css");
		$this->gtm_is_echo_data_layer_script = true;
	}

	public function action_index()
	{

		$this->template->content = View::forge('user/pc/v2/newentry/import');
		$this->wrapper_css_tag = 'contact';
		// $this->template->content->set_safe('currentUser', $user_type);
	}
	public function action_import()
	{
		if (Input::method() !== 'POST') {
			return \Response::forge(json_encode([
				'success' => false,
				'message' => 'Method not supported',
				'errors' => [],
				'data' => []
			]), 405, ['Content-Type' => 'application/json']);
		}

		$file = Input::file('file');

		if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
			return \Response::forge(json_encode([
				'success' => false,
				'message' => 'File not found or upload failed!',
				'errors' => [],
				'data' => []
			]), 400, ['Content-Type' => 'application/json']);
		}
		try {
			$import_csv = new Helper_Import_Csv();
			$data = $import_csv->parse($file['tmp_name'], $this->getHeaderMappings());

			if (!empty($data['errors'])) {
				var_dump($data['errors']);
				return \Response::forge(json_encode([
					'success' => false,
					'message' => 'Import failed due to parsing errors.',
					'errors' => $data['errors'],
					'data' => []
				]), 422, ['Content-Type' => 'application/json']);
			}
			// echo "<pre>";
			// print_r($data['data']);
			// echo "</pre>";
			// die();

			$results = [];

			foreach ($data['data'] as $entry_shop) {
				try {
					$entry_data = $this->convert_data($entry_shop);
					var_dump($entry_data);
					// die();
					$entry_id = $this->action_add_new_entry_shop($entry_data);
					// $entry_id = Model_Entryshop::insert_entryshop_test($entry_data);
					var_dump($entry_id);
					$results[] = $entry_id;
					// $results[] = $entry_data;
				} catch (\Exception $e) {
					\Log::error('Failed to insert entry: ' . $e->getMessage());
				}
			}

			$this->dd($results);

			return \Response::forge(json_encode([
				'success' => true,
				'message' => 'Add file csv success',
				'errors' => [],
				'data' => $results
			]), 200, ['Content-Type' => 'application/json']);
		} catch (\Exception $e) {
			return \Response::forge(json_encode([
				'success' => false,
				'message' => 'Import failed: ' . $e->getMessage(),
				'errors' => [],
				'data' => []
			]), 500, ['Content-Type' => 'application/json']);
		}
	}

	public function convert_data(array $entry_shop)
	{
		$entry_shop['prefecture'] = $this->get_prefecture_id($entry_shop['prefecture']);
		$sales_area        = Model_AllArea::getAjaxAreaList($entry_shop['prefecture']);
		$entry_shop['mainarea'] = $sales_area[0]['id'];

		$business_id = $this->get_business_id($entry_shop['business_id']);
		$entry_shop['business_id'] = $business_id;

		$credit_id = $this->get_credit_id($entry_shop['credit']);
		if (!isset($entry_shop['credit']) || !is_array($entry_shop['credit'])) {
			$entry_shop['credit'] = [];
		}
		$entry_shop['credit'][$credit_id] = $credit_id;

		$enable_place_id = $this->get_enable_place_id($entry_shop['enable_place']);
		if (!isset($entry_shop['enable_place']) || !is_array($entry_shop['enable_place'])) {
			$entry_shop['enable_place'] = [];
		}
		$entry_shop['enable_place'][] = $enable_place_id;

		// 
		// $entry_shop['working_time'];
		list($open_time, $close_time) = explode('～', $entry_shop['working_time']);

		$entry_shop['open_time'] = $open_time;
		$entry_shop['close_time'] = $close_time;
		unset($entry_shop['working_time']);

		// $entry_shop['most_cheap_price'];
		// minutes
		preg_match('/\d+(?=分)/', $entry_shop['most_cheap_price'], $minutesMatch);
		$minutes = isset($minutesMatch[0]) ? (int)$minutesMatch[0] : null;

		// money
		preg_match('/\d+(?=円)/', $entry_shop['most_cheap_price'], $priceMatch);
		$price = isset($priceMatch[0]) ? (int)$priceMatch[0] : null;

		$entry_shop['most_cheap_price_minutes'] = $minutes;
		$entry_shop['most_cheap_price'] = $price;
		unset($entry_shop['most_cheap_price']);

		$entry_shop['test_flg'] = 0;

		return $entry_shop;
	}

	/**
	 * Returns an array of header mappings for importing data
	 *
	 * This array maps the header names of the CSV file to the corresponding
	 * field names in the database.
	 *
	 * @return array
	 */
	protected function getHeaderMappings()
	{
		return [
			'shop_id' => 'ログインID',
			'password' => 'パスワード',
			'password_confirm' => 'パスワード確認用',
			'prefecture' => '都道府県',
			'mainarea' => 'メインエリア',
			'business_id' => '業種項目',
			'enable_place' => '営業形態',
			'staff_name' => '担当者名',
			'staff_email' => '担当者メール',
			'shop_name' => '店名',
			'shop_phonetic' => '店名フリガナ',
			'tel' => '電話番号',
			'working_time' => '営業時間',
			'most_cheap_price' => '最安値料金',
			'credit' => '利用可能クレカ',
			'pc_url' => 'パソコン版HP',
			'sp_url' => 'スマホ版HP',
		];
	}

	public function action_process()
	{
		// get data from file upload
		// validate data upload
		// call action_add_new_entry to create new entry
		// call action_add_new_shop to create new shop
		// call action_add_new_coupon to create new coupon
		// call action_add_new_job_offer to create new job offer
		// return response to client
	}

	public function action_add_new_entry_shop(array $data)
	{
		// $user_type = Model_User_Newentry::NORMAL;
		// $test_flg = 0;
		// $data['searchtext'] = '';
		// $data['search_pref'] = 1;
		// $data['planStatus'] = 0;
		// $data['new_plan']   = 0;
		// $data['shopAlias'] = '';
		// $data['open_time'] = '0';
		// $data['close_time'] = '12';
		// $data['register_open_time'] = '0';
		// $data['register_close_time'] = '12';
		// $data['publishEmail'] = '';
		// $data['shop_pr_title'] = '';
		// $data['commentForPc'] = '';
		// $data['most_cheap_price_minutes'] = '100';
		// $data['access_text'] = '';
		// $data['access_time'] = '';
		// $data['commit'] = '';

		// $data['prefecture'] = $this->get_prefecture_id($data['prefecture']);
		// $sales_area        = Model_AllArea::getAjaxAreaList($data['prefecture']);
		// $data['mainarea'] = $sales_area[0]['id'];

		// $business_id = $this->get_business_id($data['business_id']);
		// $data['business_id'] = $business_id;

		// $credit_id = $this->get_credit_id($data['credit']);
		// if (!isset($data['credit']) || !is_array($data['credit'])) {
		// 	$data['credit'] = [];
		// }
		// $data['credit'][$credit_id] = $credit_id;

		// $enable_place_id = $this->get_enable_place_id($data['enable_place']);
		// if (!isset($data['enable_place']) || !is_array($data['enable_place'])) {
		// 	$data['enable_place'] = [];
		// }
		// $data['enable_place'][] = $enable_place_id;

		$data['test_flg'] = 0;
		// var_dump($data);
		$result = Model_Entryshop::insertEntryShop($data);
		var_dump($result);


		// $fieldset   = Model_User_Newentry::makeFieldsetImport($data);
		// $data_format = Model_User_Newentry::trimOrPregReplacePostData($data, $user_type);
		// list($data_format, $fieldset) = Model_User_Newentry::getCheckHttpProtocol($data, $fieldset);

		// $result = Model_Entryshop::insertEntryShopFunc($data, $test_flg, $user_type);

		if (empty($result)) {
			return 0;
		}
		return $result[0];
	}

	public function action_add_new_shop(string $shop_id)
	{
		$shop_data = Model_Agent_Entryshop::get_Entryshopdata($shop_id);
		// 大エリアリスト
		$large_area_list = array_column(Model_LargeArea::get_large_area_list(), 'name', 'id');
		// 都道府県リスト
		$pref_list = array_column(Model_Pref::get_pref_datas(), 'name', 'id');
		// 業種リスト
		$businesslist = array_column(Model_Business::getBusinessList(), 'name', 'id');
		if (empty($shop_data)) {
			throw new HttpNotFoundException;
		}
		$shop_data['area_infos'] = Model_Agent_Shoparea::get_area_infos_entry($shop_id); //掲載エリア表示用

		$shop_data['start_area'] = isset($pref_list[$shop_data['pref_id']]) ? $pref_list[$shop_data['pref_id']] . $shop_data['name'] : '';

		try {
			//店舗NEWID存在チェック
			$chk_shopid = true;
			if (!Func_App::isNullOrEmpty($shop_data['shop_id'])) {
				$chk_shopid = Service_Import_Csv_Shop::check_shop($shop_data['shop_id']);
			}
			if ($chk_shopid) {

				\DB::start_transaction();	//トランザクションスタート
				//非表示許可の判別
				$shop_data['active_flg']  = Model_Shop::ACTIVEFLG_ON; // 非表示許可ボタンで[0]（非表示）

				list($insert_data, $insert_shoparea_data) = Service_Import_Csv_Shop::make_fieldset($shop_data);

				$crypt = Crypt::forge('mensest');
				//INSERT処理
				$insert_shop = Model_Agent_Shop::insert_shopdata($insert_data);

				// 登録できない為、catchさせる
				if (!$insert_shop) throw new Exception('登録失敗');

				// 一括上位予約データを準備（プラン特典のみ：スコア考慮なし）
				Model_Common_Manager_Shopallupdatemanager::forge($insert_shop)->timetable->adjust_times(false);


				Model_Entry::insert_data($shop_data['shop_id'], $crypt->decode($shop_data['password'], CONST_CRYPT_KEY_PWD_ONLY));
				\DB::commit_transaction();
				\DB::start_transaction();
				Model_Shoparea::updateData($shop_data['shop_id'], $insert_shoparea_data, $shop_data['shop_area_id']);

				// バニラ求人有り
				// if ($shop_data['qzin_id']) {
				// 	Model_Linkqzin::insertData([
				// 		'deli_id' => $insert_shop,
				// 		'qzin_id' => $shop_data['qzin_id'],
				// 	]);
				// }
				// メンズバニラ求人有り
				// if ($shop_data['mens_id']) {
				// 	Model_Linkmens::insertData([
				// 		'deli_id' => $insert_shop,
				// 		'mens_id' => $shop_data['mens_id'],
				// 	]);
				// }

				//掲載許可後に掲載済みに変更（active_flgを「0」へ）
				$update['id'] = $shop_data['id'];              //id
				$update['active_flg'] = Model_Entryshop::AUTH_FLG_YES; //掲載済み
				Model_Agent_Entryshop::save_entryshop($update);
				\DB::commit_transaction();	//判定OKならコミット
				// お店紹介titleとお店紹介textを作成 
				\DB::start_transaction();
				$shop_data = Model_Shop::get_shops_data([$shop_data['shop_id']])[0];
				$picmovie_repository = new \Repository_Picmovie();
				$picmov_data = $picmovie_repository->get_picmov_data($shop_data['id'], $shop_data['shop_id']);
				$pic_movie = new Service_Picmovie([
					'shop_autonum' => $shop_data['id'],
					'shop_id' => $shop_data['shop_id'],
				]);
				$intro_title_byte = 80;
				$intro_msg_byte = 800;
				$intro_title = $shop_data['shop_msg_title'];
				$intro_msg = $shop_data['shop_msg'];
				if (strlen(mb_convert_encoding($shop_data['shop_msg_title'], 'SJIS', 'UTF-8')) > $intro_title_byte) {
					$intro_title = mb_convert_encoding(mb_strcut(mb_convert_encoding($shop_data['shop_msg_title'], 'SJIS', 'UTF-8'), 0,  $intro_title_byte, 'SJIS'), 'UTF-8', 'SJIS');
				}
				if (strlen(mb_convert_encoding($shop_data['shop_msg'], 'SJIS', 'UTF-8')) > $intro_msg_byte) {
					$intro_msg = mb_convert_encoding(mb_strcut(mb_convert_encoding($shop_data['shop_msg'], 'SJIS', 'UTF-8'), 0, $intro_msg_byte, 'SJIS'), 'UTF-8', 'SJIS');
				}
				$col_list = $pic_movie->format_postdata_for_shop_introduction_configs_store(['intro_title' => $intro_title, 'intro_text' => $intro_msg], $picmov_data);
				$pic_movie->save_shopintroductionconfig($col_list, $picmov_data);
				\DB::commit_transaction();
				$msg = '掲載をしました';
			} else {
				$msg = '店舗IDが重複しています。掲載できませんでした';
				$shop_data['shop_id'] = '';
			}
		} catch (Exception $e) {
			//メール送信rejectフラグ
			$reject_flg = true;
			$shop_data['shop_id'] = '';
			$db_error = $e->getMessage(); //DBエラーメッセージ取得
			\DB::rollback_transaction(); //エラーならロールバック
			$msg = '掲載できませんでした' . $db_error;
		}
	}
	// public function action_add_new_coupon() {}
	// public function action_add_new_job_offer() {}

	private function get_prefecture_id(string $pref_name)
	{
		$pref_list = Model_Pref::get_pref_datas();

		foreach ($pref_list as $key => $pref) {
			if ($pref_name = $pref['name']) {
				return $pref['id'];
			}
		}
	}
	private function get_enable_place_id(string $enable_place_name)
	{
		$enable_place_info = Config::get('app.enable_place');

		foreach ($enable_place_info as $key => $enable_place) {
			if ($enable_place_name == $enable_place) {
				return $key;
			}
		}
	}

	private function get_credit_id(string $credit_name)
	{
		$credit_list   = Model_Shop::getEnableCreditArray();
		$credit_list   = Func_App::create_valueSum1Array($credit_list);

		foreach ($credit_list as $key => $credit) {
			if ($credit_name == $credit) {
				return $key;
			}
		}
	}

	private function get_business_id(string $business_name)
	{
		$business_list = array_column(Model_Business::getBusinessList(), 'name', 'id');

		foreach ($business_list as $key => $name) {
			if ($business_name = $name) {
				return $key;
			}
		}
	}

	public function dd($value)
	{
		var_dump($value);
		die();
	}
	public function dump($value)
	{
		var_dump($value);
	}

	public function sample()
	{
		return [
			'shopId' =>  'abctest1',
			'password' =>  '12345678',
			'passwordConfirm' =>  '12345678',
			'prefecture' =>  '1',
			'mainarea' =>  '00223',
			'searchtext' =>  '',
			'search_pref' =>  '',
			'mainAreaText' =>  '函館',
			'business_id' =>  '1',
			'enable_place' =>
			[
				0 =>  '0',
			],
			'access_text' =>  '',
			'access_time' =>  '',
			'staffName' =>  'admin',
			'staffEmail' =>  'admin@thd.com',
			'shopName' =>  'メンエス',
			'shopPhonetic' =>  'キューエージドウテストテンポ',
			'shopAlias' =>  '',
			'tel' =>  '0909090909',
			'open_time' =>  '1',
			'close_time' =>  '11',
			'register_open_time' =>  '',
			'register_close_time' =>  '',
			'mostCheapPriceMinutes' =>  '50',
			'mostCheapPrice' =>  '1000',
			'credit' =>
			[
				2 =>  '2',
			],
			'creditText' =>  '',
			'pcUrl' =>  'https://mens-est.internal/newentry/',
			'spUrl' =>  'https://mens-est.internal/newentry/',
			'publishEmail' =>  '',
			'shop_pr_title' =>  '',
			'commentForPc' =>  '',
			// 'confirm' =>  'free',
			// 'csrf_mens_est_token' =>  '219ba7402334125a7f243be7426e8a463cb9b473d57b6296870838050cd8f0c7dfd30ee9e4770efb355de29423e78320d4dbcfe8a3a4f7ded81676a437505379',
			// 'commit' =>  'free',
			// 'button_flg' =>  '2',
			'planStatus' => 0,
			'new_plan' => 0,
		];
	}
}
