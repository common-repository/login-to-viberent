<?php
session_start();
/* Template name: Viberent item-based layout */
require_once('item_page.php');
get_header();
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
<script>
	jQuery('document').ready(function($) {
		$(".item-category-box").each(function(index, elem) {
			$('select#period option[value="<?php if (isset($_POST["period"]) && isset($_POST["viberent_nonce"])) {
												wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
												echo esc_js(sanitize_text_field($_POST["period"]));
											} elseif (isset($_POST["rentalratesName"]) && isset($_POST["viberent_nonce"])) {
												wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
												echo esc_js(sanitize_text_field($_POST["rentalratesName"]));
											} else {
												echo esc_js($firstRental_period);
											} ?>"]').attr("selected", true);
		});
		<?php if (isset($_POST["rentalratesName"]) && isset($_POST["viberent_nonce"])) {
			wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
			$rental_period = sanitize_text_field($_POST["rentalratesName"]);
		?>
			$('#my-dates').click();
		<?php } else {
			$rental_period = sanitize_text_field($firstRental_period);
		} ?>
		var totalQuantity = $("#totalQuantity").val();
		if (totalQuantity > 0) {
			$(".btn_mycart").find("span.has-badge").attr('data-count', totalQuantity);
		} else {
			$(".btn_mycart").find("span.has-badge").attr('data-count', '0');
		}
	});
</script>
<div class="viberent_item_layout">
	<div class="viberent_main_container container p-5">
		<div class="d-flex justify-content-end align-items-center viberent_carts mb-3">
			<a class="btn_mycart pt-1" href="<?php echo esc_url(site_url() . "/my-cart/") ?>">
				<span class="fa-stack fa-2x has-badge cart" data-count="0">
					<i class="fa fa-shopping-cart fa-stack-1x"></i>
				</span>
			</a>
		</div>
		<div class="row">
			<div class="col-sm-12 col-md-4 col-lg-3">
				<div class="rental-period">
					<form method="post" class="pt-3">
						<label for="period">Rental Period</label>
						<select name="period" id="period" class="period" required>
							<?php
							foreach ($respperiod as $retrieved_period) {
							?>
								<option value="<?php echo esc_attr($retrieved_period["name"]); ?>"><?php echo esc_html($retrieved_period["name"]); ?></option>
								<script>
									function convert(str) {
										var date = new Date(str),
											mnth = ("0" + (date.getMonth() + 1)).slice(-2),
											day = ("0" + date.getDate()).slice(-2);
										return [date.getFullYear(), mnth, day].join("-");
									}

									function formatDateNew(date) {
										var d = new Date(date),
											month = '' + (d.getMonth() + 1),
											day = '' + d.getDate(),
											year = d.getFullYear();
										if (month.length < 2)
											month = '0' + month;
										if (day.length < 2)
											day = '0' + day;
										return [year, month, day].join('-');
									}
								jQuery('document').ready(function($) {
										var dtToday = new Date();
										var month = dtToday.getMonth() + 1;
										var day = dtToday.getDate();
										var year = dtToday.getFullYear();
										if (month < 10)
											month = '0' + month.toString();
										if (day < 10)
											day = '0' + day.toString();
										var maxDate = year + '-' + month + '-' + day;
										$('#start-date').attr('min', maxDate);
										$('#end-date').attr('min', maxDate);
										$('select#period').change(function() {
											var period = $(this).val().trim();
											var rental_period = '<?php echo esc_js($retrieved_period["name"]) ?>';
											if (rental_period == period) {
												var start_date = $("#start-date").val();
												var exclude = '<?php echo esc_js($retrieved_period["value"]) ?>';
												var timeTypeId = '<?php echo esc_js($retrieved_period["timeTypeId"]) ?>';
												if (period == "Exclude Sat / Sun") {
													start_date = new Date(start_date);
													var endDate = "",
														noOfDaysToAdd = parseInt(exclude),
														count = 1;
													if (start_date.getDay() == 6 || start_date.getDay() == 0) {
														count = 0;
														while (count < noOfDaysToAdd) {
															endDate = new Date(start_date.setDate(start_date.getDate() + 1));
															if (endDate.getDay() != 0 && endDate.getDay() != 6) {
																count++;
															}
														}
													} else {
														while (count < noOfDaysToAdd) {
															endDate = new Date(start_date.setDate(start_date.getDate() + 1));
															if (endDate.getDay() != 0 && endDate.getDay() != 6) {
																count++;
															}
														}
													}
													$("#end-date").val(convert(endDate));
												} else if (period == "Exclude Sat / Sun Daily") {
													var start_date = new Date(start_date);
													var endDate = "";
													if (start_date.getDay() == 6) {
														endDate = new Date(start_date.setDate(start_date.getDate() + 2));
													} else if (start_date.getDay() == 0) {
														endDate = new Date(start_date.setDate(start_date.getDate() + 1));
													} else {
														endDate = new Date(start_date.setDate(start_date.getDate() + parseInt(exclude) - 1));
													}
													$("#end-date").val(convert(endDate));
												} else if (period == "Exclude Sun") {
													start_date = new Date(start_date.replace(/-/g, "/"));
													var endDate = "",
														noOfDaysToAdd = parseInt(exclude),
														count = 1;
													if (start_date.getDay() == 0) {
														endDate = new Date(start_date.setDate(start_date.getDate() + parseInt(exclude)));
													} else {
														while (count < noOfDaysToAdd) {
															endDate = new Date(start_date.setDate(start_date.getDate() + 1));
															if (endDate.getDay() != 0) {
																//Date.getDay() gives weekday starting from 0(Sunday) to 6(Saturday)
																count++;
															}
														}
													}
													$("#end-date").val(convert(endDate));
												} else if (period == "Daily") {
													var someDate = new Date(start_date);
													var dateFormated = someDate.setDate(someDate.getDate());
													var dateFormated = someDate.toISOString().substr(0, 10);
													$("#end-date").val(dateFormated);
												} else if (period == "Monthly") {
													var getFullYear = start_date.substr(0, 4);
													var getMonth = start_date.substr(5, 2);
													var getDate = start_date.substr(8, 2);
													var endDate = "";
													getMonth = parseInt(getMonth)
													var lastday = function(y, m) {
														return new Date(y, m + 0, 0).getDate();
													}
													if (getDate == 31 && getMonth == 01) {
														var exclude_date = 29;
													} else if (getDate == 31 && ((getMonth == 03) || (getMonth == 05) || getMonth == 08 || getMonth == 10 || getMonth == 11)) {
														var exclude_date = 30;
													} else if (getDate == 30 && getMonth == 09) {
														var exclude_date = 30;
													} else {
														var exclude_date = lastday(getFullYear, getMonth);
													}
													var someDate = new Date(start_date);
													var dateFormated = someDate.setDate(someDate.getDate() + (parseInt(exclude_date) - 1));
													var dateFormated = someDate.toISOString().substr(0, 10);
													$("#end-date").val(dateFormated);
												} else {
													var dateFormated;
													if (timeTypeId == 2) {
														dateFormated = moment(start_date).add(exclude, 'hours').format('YYYY-MM-DD');
													} else {
														var someDate = new Date(start_date);
														dateFormated = someDate.setDate(someDate.getDate() + parseInt(exclude) - 1);
														dateFormated = someDate.toISOString().substr(0, 10);
													}
													$("#end-date").val(dateFormated);
												}
											}
											$('#my-dates').click();
										});
										$('#start-date').change(function() {
											var start_date = $(this).val();
											var rental_period = '<?php echo esc_js($retrieved_period["name"]) ?>';
											var exclude = '<?php echo esc_js($retrieved_period["value"]) ?>';
											var timeTypeId = '<?php echo esc_js($retrieved_period["timeTypeId"]) ?>';
											var period = $("#period").val();
											if (rental_period == period) {
												if (period == "Exclude Sat / Sun") {
													start_date = new Date(start_date);
													var endDate = "",
														noOfDaysToAdd = parseInt(exclude),
														count = 1;
													if (start_date.getDay() == 6 || start_date.getDay() == 0) {
														count = 0;
														while (count < noOfDaysToAdd) {
															endDate = new Date(start_date.setDate(start_date.getDate() + 1));
															if (endDate.getDay() != 0 && endDate.getDay() != 6) {
																count++;
															}
														}
													} else {
														while (count < noOfDaysToAdd) {
															endDate = new Date(start_date.setDate(start_date.getDate() + 1));
															if (endDate.getDay() != 0 && endDate.getDay() != 6) {
																count++;
															}
														}
													}
													$("#end-date").val(convert(endDate));
												} else if (period == "Exclude Sat / Sun Daily") {
													var start_date = new Date(start_date);
													var endDate = "";
													if (start_date.getDay() == 6) {
														endDate = new Date(start_date.setDate(start_date.getDate() + 2));
													} else if (start_date.getDay() == 0) {
														endDate = new Date(start_date.setDate(start_date.getDate() + 1));
													} else {
														endDate = new Date(start_date.setDate(start_date.getDate() + parseInt(exclude) - 1));
													}
													$("#end-date").val(convert(endDate));
												} else if (period == "Exclude Sun") {
													start_date = new Date(start_date.replace(/-/g, "/"));
													var endDate = "",
														noOfDaysToAdd = parseInt(exclude),
														count = 1;

													if (start_date.getDay() == 0) {
														endDate = new Date(start_date.setDate(start_date.getDate() + parseInt(exclude)));
													} else {
														while (count < noOfDaysToAdd) {
															endDate = new Date(start_date.setDate(start_date.getDate() + 1));
															if (endDate.getDay() != 0) {
																//Date.getDay() gives weekday starting from 0(Sunday) to 6(Saturday)
																count++;
															}
														}
													}
													$("#end-date").val(convert(endDate));
												} else if (period == "Daily") {
													var someDate = new Date(start_date);
													var dateFormated = someDate.setDate(someDate.getDate());
													var dateFormated = someDate.toISOString().substr(0, 10);
													$("#end-date").val(dateFormated);
												} else if (period == "Monthly") {
													var getFullYear = start_date.substr(0, 4);
													var getMonth = start_date.substr(5, 2);
													var getDate = start_date.substr(8, 2);

													var endDate = "";
													getMonth = parseInt(getMonth)
													var lastday = function(y, m) {
														return new Date(y, m + 0, 0).getDate();
													}

													if (getDate == 31 && getMonth == 01) {
														var exclude_date = 29;
													} else if (getDate == 31 && ((getMonth == 03) || (getMonth == 05) || getMonth == 08 || getMonth == 10 || getMonth == 11)) {
														var exclude_date = 30;
													} else if (getDate == 30 && getMonth == 09) {
														var exclude_date = 30;
													} else {
														var exclude_date = lastday(getFullYear, getMonth);
													}
													var someDate = new Date(start_date);
													var dateFormated = someDate.setDate(someDate.getDate() + (parseInt(exclude_date) - 1));
													var dateFormated = someDate.toISOString().substr(0, 10);
													$("#end-date").val(dateFormated);
												} else {
													var dateFormated;
													if (timeTypeId == 2) {
														dateFormated = moment(start_date).add(exclude, 'hours').format('YYYY-MM-DD');
													} else {
														var someDate = new Date(start_date);
														dateFormated = someDate.setDate(someDate.getDate() + parseInt(exclude) - 1);
														dateFormated = someDate.toISOString().substr(0, 10);
													}
													$("#end-date").val(dateFormated);
												}
											}
											$('#my-dates').click();
										});
									});
								</script>
							<?php
							}
							?>
						</select>
						<label for="start-date">Start Date</label>
						<input type="date" data-date="" data-date-format="<?php echo esc_attr($date_Format); ?>" value="<?php if (isset($_POST['start-date']) && isset($_POST["viberent_nonce"])) {
																															wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
																															echo esc_attr(sanitize_text_field($_POST['start-date']));
																															$_SESSION["start-date"] = sanitize_text_field($_POST["start-date"]);
																														} else {
																															echo esc_attr($startFrom_date);
																														} ?>" id="start-date" name="start-date" placeholder="Select Start Date" required>
						<label for="end-date">End Date</label>
						<input type="date" data-date="" data-date-format="<?php echo esc_attr($date_Format); ?>" value="<?php if (isset($_POST['end-date']) && isset($_POST["viberent_nonce"])) {
																															wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
																															echo esc_attr(sanitize_text_field($_POST['end-date']));
																															$_SESSION["end-date"] = sanitize_text_field($_POST["end-date"]);
																														} elseif (isset($_SESSION["end-date"])) {
																															echo sanitize_text_field($_SESSION['end-date']);
																														} else {
																															echo esc_attr($startEnd_date);
																														} ?>" id="end-date" name="end-date" placeholder="Select End Date" required>
						<input type="hidden" class="viberent_nonce" name="viberent_nonce" value="wp_create_nonce( 'viberent_rental_filter' )" />
						<button class="p-0" type="submit" name="my-dates" id="my-dates">Check Availability</button>
					</form>
				</div>
			</div>
			<script>
				jQuery("input[type='date']").on("change", function() {
					this.setAttribute(
						"data-date",
						moment(this.value, "YYYY-MM-DD")
						.format(this.getAttribute("data-date-format"))
					)
				}).trigger("change")
			</script>
			<div class="col-sm-12 col-md-8 col-lg-9">
				<h5 class="new-booking">New Booking: <span>
						<?php
						if (isset($_POST["period"]) && isset($_POST["viberent_nonce"])) {
							wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
							$rentalPeriod = sanitize_text_field($_POST["period"]);
						} else {
							$rentalPeriod = sanitize_text_field($firstRental_period);
						}
						$my_from_date = gmdate("Y-m-d");
						$my_to_date = gmdate("Y-m-d", strtotime($firstRental_showValue));
						$show_from_date = gmdate($dateFormat);
						$show_to_date = gmdate($dateFormat, strtotime($firstRental_showValue));
						if (isset($_POST["my-dates"]) && isset($_POST["viberent_nonce"])) {
							wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
							$my_from_date = sanitize_text_field($_POST["start-date"]);
							$my_to_date = sanitize_text_field($_POST["end-date"]);

							$show_from_date = gmdate($dateFormat, strtotime($my_from_date));
							$show_to_date = gmdate($dateFormat, strtotime($my_to_date));

							$start_from_date = gmdate('Y-m-d', strtotime($my_from_date));
							$end_to_date = gmdate('Y-m-d', strtotime($my_to_date));
						} else {
							$start_from_date = gmdate("Y-m-d");
							$end_to_date = gmdate("Y-m-d", strtotime($firstRental_showValue));
						}
						echo esc_html($show_from_date . ' - ' . $show_to_date);
						?>
					</span> </h5>
				<div class="row">
					<?php
					if (isset($resp2)) {
						foreach ($resp2 as $retrieved_data) {
							$curlavail = wp_remote_get($viberent_api_url . 'Item/item-availability?itemGUID=' . $retrieved_data["itemGUID"] . '&companyid=' . $companyID . '&fromDate=' . $my_from_date . '&todate=' . $my_to_date . '&PeriodTypeId=' . $periodTypeId . '&locationID=0', $api_args);
							if (is_wp_error($curlavail) || wp_remote_retrieve_response_code($curlavail) != 200) {
								return false;
							}
							$responseavail = wp_remote_retrieve_body($curlavail);
							$respavail = json_decode($responseavail, 1);
					?>
							<div class="col-12 col-md-6 col-lg-4">
								<div class="item-category-box ng-star-inserted p-3">
									<form class="ajax-layoutbase-form">
										<div class="item-info">
											<div class="item-details">
												<div class="product-title">
													<h5 class="product-name"><?php echo esc_html($retrieved_data["itemName"]); ?></h5>
													<div class="product-image">
														<img src=<?php
																	if (empty($retrieved_data["images"])) {
																		echo esc_url($full_path . 'assets/images/no_image.png');
																	} else {
																		$count = 0;
																		foreach ($retrieved_data["images"] as $image) {
																			if ($count == 0) {
																				echo esc_url($image['blobUrl']);
																			}
																			$count++;
																		}
																	}
																	?>>
													</div>

													<h5 class="product-pricing m-0">
														<?php
														if (isset($_POST["period"]) && isset($_POST["viberent_nonce"])) {
															wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
															$my_rental_period = sanitize_text_field($_POST["period"]);
														} else {
															$my_rental_period = sanitize_text_field($firstRental_period);
														}
														if (isset($_POST["my-dates"]) && isset($_POST["viberent_nonce"])) {
															wp_verify_nonce($_POST["viberent_nonce"], 'viberent_rental_filter');
															$is_present = 0;
															$i = 1;
															foreach ($retrieved_data["rentalRates"] as $rentalRate) {
																if ($rentalRate['rentalratesName'] == $my_rental_period) {
																	echo esc_html($currencysymbol);
																	echo esc_html(number_format($rentalRate['rentalratesvalue'], 2));
																	echo " : " . esc_html($my_rental_period);
																	$is_present = 1;
																	if ($i == 1) {
														?>
																		<input type="hidden" name="price" class="rentalratesvalue" value="<?php echo esc_attr($rentalRate['rentalratesvalue']); ?>" />
																<?php
																	}
																	$i++;
																} ?>

															<?php
															}
															if ($is_present !== 1) {

															?><span class="price-not-available"><?php echo "pricing not available"; ?></span>
																<input type="hidden" name="price" class="rentalratesvalue" value="<?php echo 0; ?>" />
																<?php
															}
														} else {
															$is_daily = 0;
															$i = 1;
															foreach ($retrieved_data["rentalRates"] as $rentalRate) {
																if ($rentalRate['rentalratesName'] == $firstRental_period) {
																	echo esc_html($currencysymbol);
																	echo esc_html(number_format($rentalRate['rentalratesvalue'], 2));
																	echo " : " . esc_html($firstRental_period);
																	$is_daily = 1;
																	if ($i == 1) {
																?>
																		<input type="hidden" name="price" class="rentalratesvalue" value="<?php echo esc_attr($rentalRate['rentalratesvalue']); ?>" />
																<?php
																	}
																	$i++;
																}
																?>
															<?php
															}
															if ($is_daily !== 1) {
															?><div class="price-not-available"><?php echo "pricing not available"; ?></div>
																<input type="hidden" name="price" class="rentalratesvalue" value="<?php echo 0; ?>" />
														<?php
															}
														}
														?>
													</h5>
												</div>
												<p class="product-available pt-0">
													<?php
													echo "Available : <span class='product_available'>" . esc_html($respavail[0]['available']) . "</span>";
													?>
												</p>
												<div class="add-to-cart-component buy-items-btn ng-star-inserted">
													<input type="number" class="product-quantity" name="quantity" min="1" value="1" size="2" max="<?php echo esc_html($respavail[0]['available']); ?>" />
													<img src="<?php echo esc_url(plugin_dir_url('loading.gif', __FILE__) . 'login-to-viberent/assets/images/loading.gif'); ?>" class="loading" />
													<i class="fa fa-cart-arrow-down add_to_cart_icon"></i>
													<input type="submit" name="add_to_cart" value="Add to Cart" class="btnAddAction" />
												</div>
												<div class="product-quantity-message">
													<?php
													$count = 0;
													if (isset($_SESSION["cart_item"])) {
														foreach ($_SESSION["cart_item"] as $k => $item) {
															if ($item["productAvailble"] >= $item["quantity"]) {
																$productAvailable = $item["quantity"];
															} else {
																$productAvailable = $item["productAvailble"];
															}
															if ($retrieved_data['itemGUID'] == $item['GUID'] && $rentalPeriod == $item['rental_period']) {
																$count++;
															}
														}
													?>
													<?php
													}
													if ($count > 0) {
														echo "<div class='itemQuantityAvailablediv'><b>item(s) added to cart</b></bb></div>";
													}
													?>
												</div>
												<div class="item-summary">
													<p class="minimize"><?php echo esc_html($retrieved_data["itemDescription"]); ?></p>
												</div>
											</div>
										</div>
										<input type="hidden" class="productimage" name="image" value="<?php if (empty($retrieved_data["images"])) {
																											echo esc_attr($full_path . 'assets/images/no_image.png');
																										} else {
																											$count = 0;
																											foreach ($retrieved_data["images"] as $image) {
																												if ($count == 0) {
																													echo esc_attr($image['blobUrl']);
																												}
																												$count++;
																											}
																										}
																										?>" />
										<input type="hidden" class="productAvailable" name="productAvailable" value="<?php echo esc_attr($respavail[0]['available']); ?>" />
										<input type="hidden" class="periodUnits" name="periodUnits" value="<?php echo esc_attr($respavail[0]['periodUnits']); ?>" />
										<input type="hidden" class="itemCode" name="itemCode" value="<?php echo esc_attr($retrieved_data['itemCode']); ?>" />
										<input type="hidden" class="itemGUID" name="itemGUID" value="<?php echo esc_attr($retrieved_data['itemGUID']); ?>" />
										<input type="hidden" class="hireTypeID" name="hireTypeID" value="<?php echo esc_attr($retrieved_data['hireTypeID']); ?>" />
										<input type="hidden" class="locationID" name="locationID" value="<?php echo esc_attr($retrieved_data['locationID']); ?>" />
										<input type="hidden" class="itemName" name="itemName" value="<?php echo esc_attr($retrieved_data["itemName"]); ?>" />
										<input type="hidden" class="rentalratesName" name="rentalratesName" value="<?php echo esc_attr($rentalPeriod); ?>" />
										<input type="hidden" class="categoryName" name="categoryName" value="all" />
										<input type="hidden" class="taxrate" name="taxrate" value="<?php echo esc_attr($retrieved_data['taxrate']); ?>" />
										<input type="hidden" class="startDate" name="start-date" value="<?php
																										if (isset($_SESSION["start-date"])) {
																											echo esc_attr(sanitize_text_field($_SESSION["start-date"]));
																										} else {
																											echo esc_attr($start_from_date);
																										}
																										?>" />
										<input type="hidden" class="endDate" name="end-date" value="<?php
																									if (isset($_SESSION["end-date"])) {
																										echo esc_attr(sanitize_text_field($_SESSION["end-date"]));
																									} else {
																										echo esc_attr($end_to_date);
																									}
																									?>" />
										<input type="hidden" class="sessionID" name="sessionID" value="<?php echo esc_attr(trim($retrieved_data['itemGUID'] . $rentalPeriod)); ?>" />
										<input type="hidden" class="viberent_nonce" name="viberent_nonce" value="wp_create_nonce( 'viberent_rental_filter' )" />
									</form>
								</div>
							</div>
						<?php
						}
						?>

				</div>
				<?php
						$are_pages = $retrieved_data["totalRows"] % $viberent_pageSize;
						$not_final_pages = intval($retrieved_data["totalRows"] / $viberent_pageSize);
						if ($are_pages == 0) {
							$total_pages = (int)$not_final_pages;
						} else {
							$total_pages = (int)$not_final_pages + 1;
						}
					}
					if (isset($total_pages)) {
				?>
				<div class="viberentPagination">
					<ul>
					<?php
						if ($total_pages > 1) {
							if ($page_nos >= 2) {
								echo "<li class='prev'><a href='" . esc_url(wp_nonce_url(site_url() . "/" . $mypagename . "?pageno=" . ($page_nos - 1), 'viberent_page_nonce')) . "'><span>Prev</span></a></li>";
							}
							for ($x = 1; $x <= $page_nos; $x++) {
								$query['pageno'] =  $x;
								if ($x == $page_nos) {
									echo "<li class='active'><a href='" . esc_url(wp_nonce_url(site_url() . "/" . $mypagename . "?pageno="
										. $x, 'viberent_page_nonce')) . "'><span>" . esc_html($x) . " </span></a></li>";
								} else {
									echo "<li><a href='" . esc_url(wp_nonce_url(site_url() . "/" . $mypagename . "?pageno=" . $x, 'viberent_page_nonce')) . "'><span>   
                                                    " . esc_html($x) . " </span></a></li>";
								}
							}
							if ($page_nos < $total_pages) {
								echo '<li class="disabled"><span>...</span></li>';
								echo "<li><a href='" . esc_url(wp_nonce_url(site_url() . "/" . $mypagename . "?pageno=" . ($page_nos + 1), 'viberent_page_nonce')) . "'><span>Next</span></a></li>";
							}
						}
					}
					?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>