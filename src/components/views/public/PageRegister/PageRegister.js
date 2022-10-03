import React, { useState } from "react";
import { useHistory } from "react-router-dom";
import {
	Layout,
	Card,
	Form,
	Button,
	Row,
	Col,
	Image,
	Typography,
	Collapse,
	Checkbox,
	Alert,
} from "antd";
import { MinusSquareOutlined, PlusSquareOutlined } from "@ant-design/icons";
import moment from "moment";
import { description, fullwidthlogo } from "../../../providers/companyInfo";
import { GET, POSTMANUAL } from "../../../providers/useAxiosQuery";
import FloatInput from "../../../providers/FloatInput";
import FloatInputMask from "../../../providers/FloatInputMask";
import ComponentHeader from "../Components/ComponentHeader";
import optionCountryCodes from "../../../providers/optionCountryCodes";

import optionStateCodesUnitedState from "../../../providers/optionStateCodesUnitedState";
import optionStateCodesCanada from "../../../providers/optionStateCodesCanada";

import ReCAPTCHA from "react-google-recaptcha";

import { faEdit } from "@fortawesome/pro-regular-svg-icons";
import FloatSelect from "../../../providers/FloatSelect";
import FloatSelectWithDangerouslySetInnerHTML from "../../../providers/FloatSelectWithDangerouslySetInnerHTML";

export default function PageRegister({ match }) {
	let tokenReferred =
		match.params && match.params.token
			? "Bearer " + match.params.token
			: process.env.REACT_APP_API_KEY;

	const history = useHistory();
	const [collapseActiveKey, setCollapseActiveKey] = useState("1");
	const [programTypes, setProgramTypes] = useState([]);
	const [selectedProgramType, setSelectedProgramType] = useState();

	const [formData, setFormData] = useState([
		{
			step: "process",
			data: null,
		},
		{
			step: "wait",
			data: null,
		},
		{
			step: "wait",
			data: null,
		},
	]);

	const stateUS = optionStateCodesUnitedState();
	const stateCA = optionStateCodesCanada();

	const [optionState, setOptionState] = useState([]);
	const [stateLabel, setStateLabel] = useState("State");
	const [optionZip, setOptionZip] = useState();
	const [zipLabel, setZipLabel] = useState("Zip Code");

	const [optionBillingState, setOptionBillingState] = useState([]);
	const [optionBillingZip, setOptionBillingZip] = useState();
	const [billingStateLabel, setBillingStateLabel] = useState("State");
	const [billingZipLabel, setBillingZipLabel] = useState("Zip Code");

	const [captchaToken, setCaptchaToken] = useState({
		token: "",
		error: "",
	});

	const [completePurchaseErr, setCompletePurchaseErr] = useState({
		type: "",
		message: "",
	});

	GET("api/v1/acc_type", "acc_type", (res) => {
		if (res.success) {
			// console.log("acc_type", res.data);
			let data = [];

			res.data.map((item) => {
				data.push({
					label: item.description,
					value: item.id,
					policy: item.privacy && item.privacy.privacy_policy,
					amount:
						item.account_plan && item.account_plan.length > 0
							? item.account_plan[0].amount
							: 0,
				});

				return "";
			});

			// console.log("acc_type data", data);
			setProgramTypes(data);
		}
	});

	const { mutate: mutateRegister, isLoading: isLoadingRegister } = POSTMANUAL(
		tokenReferred,
		"api/v1/register",
		"register"
	);

	const handleCountry = (e, opt) => {
		if (e === "United States") {
			setOptionState(stateUS);
			setStateLabel("State");
			setOptionZip(/(^\d{5}$)|(^\d{5}-\d{4}$)/);
			setZipLabel("Zip Code");
		} else if (e === "Canada") {
			setOptionState(stateCA);
			setStateLabel("County");
			setOptionZip(/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/);
			setZipLabel("Postal Code");
		} else {
			setOptionState(stateUS);
			setStateLabel("State");
			setOptionZip(/(^\d{5}$)|(^\d{5}-\d{4}$)/);
			setZipLabel("Zip Code");
		}

		// form2.resetFields(["state"]);
	};

	const handleChangeBillingCountry = (e, opt) => {
		// console.log("e, opt", e, opt);
		if (e === "United States") {
			setOptionBillingState(stateUS);
			setOptionBillingZip(/(^\d{5}$)|(^\d{5}-\d{4}$)/);
			setBillingStateLabel("State");
			setBillingZipLabel("Zip Code");
		} else if (e === "Canada") {
			setOptionBillingState(stateCA);
			setOptionBillingZip(/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/);
			setBillingStateLabel("County");
			setBillingZipLabel("Postal Code");
		} else {
			setOptionBillingState(stateUS);
			setOptionBillingZip(/(^\d{5}$)|(^\d{5}-\d{4}$)/);
			setBillingStateLabel("State");
			setBillingZipLabel("Zip Code");
		}

		// form4.resetFields(["billing_state"]);
	};

	const onFinishInfomation = (values) => {
		let formDataTemp = formData;
		formDataTemp[0] = {
			step: "done",
			data: values,
		};
		formDataTemp[1] = {
			...formDataTemp[1],
			step: "process",
		};
		setFormData(formDataTemp);
		setCollapseActiveKey("2");
	};

	const handleApplyCoupon = () => {
		if (selectedProgramType.coupon_apply === 0) {
			if (selectedProgramType.coupon === "CCG2021") {
				let coupon = (20 / 100) * parseFloat(selectedProgramType.amount);
				let amount = parseFloat(selectedProgramType.amount) - coupon;
				// console.log("selectedProgramType", selectedProgramType, amount, coupon);
				setSelectedProgramType({
					...selectedProgramType,
					coupon_apply: 1,
					amount,
					message: "Code Successfully Applied",
				});
			} else {
				setSelectedProgramType({
					...selectedProgramType,
					coupon_apply: 1,
					message: "Invalid Code",
				});
			}
		}
	};

	const handleProceedToCheckout = () => {
		let formDataTemp = formData;
		formDataTemp[1] = {
			step: "done",
			data: selectedProgramType,
		};
		formDataTemp[2] = {
			...formDataTemp[2],
			step: "process",
		};
		setFormData(formDataTemp);
		setCollapseActiveKey("3");
	};

	const [checkboxYes, setCheckboxYes] = useState(true);
	const handleScroll = (e) => {
		// console.log("values");
		let element = e.target;
		// console.log("element.scrollHeight", element.scrollHeight);
		// console.log("element.scrollTop", element.scrollTop);
		// console.log("element.clientHeight", element.clientHeight);

		if (element.scrollHeight - element.scrollTop <= element.clientHeight) {
			setCheckboxYes(false);
		} else {
			setCheckboxYes(true);
		}
	};

	const [checkboxYesStatus, setCheckboxYesStatus] = useState(false);
	const onChangeCheckbox = (e) => {
		// console.log("e.target.checked", e.target.checked);
		setCheckboxYesStatus(e.target.checked);
	};

	const onFinishCompletePurchase = (values) => {
		let data = {
			...formData[0].data,
			...formData[1].data,
			account_type_id: formData[1].data.value,
			...values,
			link_origin: window.location.origin,
		};

		mutateRegister(data, {
			onSuccess: (res) => {
				if (res.success) {
					setCompletePurchaseErr({
						type: "success",
						message:
							"A confirmation e-mail has been send please check your inbox or your spam folder for the next step.",
					});
				} else {
					setCompletePurchaseErr({
						type: "error",
						message: res.message,
					});
				}
			},
			onError: (err) => {
				// console.log(err.response.data);
				setCompletePurchaseErr({
					type: "error",
					message: err.response.data.message,
				});
			},
		});
	};

	return (
		<Layout className="public-layout register-layout">
			<Layout.Content>
				<Row>
					<Col xs={24} sm={24} md={24}>
						<Image
							className="zoom-in-out-box"
							onClick={() => history.push("/")}
							src={fullwidthlogo}
							preview={false}
						/>

						<div className="register-sub-title">
							Educating Cancer CareGivers for their wellbeing & improved patient
							outcomes
						</div>

						<Card>
							<ComponentHeader
								title="Registration"
								subtitle="New User"
								icon={faEdit}
							/>

							<Collapse
								accordion
								expandIconPosition="end"
								activeKey={collapseActiveKey}
								onChange={(e) => setCollapseActiveKey(e)}
								expandIcon={({ isActive }) =>
									isActive ? <MinusSquareOutlined /> : <PlusSquareOutlined />
								}
							>
								<Collapse.Panel
									header={
										<>
											<div className="title">Step 1</div>
											<div className="sub-title">Complete All Fields Below</div>
										</>
									}
									key="1"
								>
									<Form
										layout="vertical"
										autoComplete="off"
										onFinish={onFinishInfomation}
									>
										<Typography.Text className="form-title">
											User's Information
										</Typography.Text>
										<Form.Item
											name="firstname"
											className="m-t-sm"
											hasFeedback
											rules={[
												{
													required: true,
													message: "This field is required.",
												},
											]}
										>
											<FloatInput label="First Name" placeholder="First Name" />
										</Form.Item>

										<Form.Item
											name="lastname"
											hasFeedback
											rules={[
												{
													required: true,
													message: "This field is required.",
												},
											]}
										>
											<FloatInput label="Last Name" placeholder="Last Name" />
										</Form.Item>

										<Form.Item
											name="username"
											hasFeedback
											rules={[
												{
													required: true,
													message: "This field is required.",
												},
											]}
										>
											<FloatInput
												label="Create Username"
												placeholder="Create Username"
											/>
										</Form.Item>

										<Form.Item
											name="email"
											hasFeedback
											rules={[
												{
													type: "email",
													message: "The input is not valid email!",
												},
												{
													required: true,
													message: "Please input your email!",
												},
											]}
										>
											<FloatInput label="Email" placeholder="Email" />
										</Form.Item>

										<Form.Item
											name="confirm_email"
											hasFeedback
											rules={[
												{
													required: true,
													message: "This field is required.",
												},
												({ getFieldValue }) => ({
													validator(_, value) {
														if (!value || getFieldValue("email") === value) {
															return Promise.resolve();
														}
														return Promise.reject(
															new Error(
																"The two emails that you entered do not match!"
															)
														);
													},
												}),
											]}
										>
											<FloatInput
												label="Confirm Email"
												placeholder="Confirm Email"
											/>
										</Form.Item>

										<Form.Item
											name="country"
											hasFeedback
											className="form-select-error"
											rules={[
												{
													required: true,
													message: "This field field is required.",
												},
											]}
										>
											<FloatSelect
												label="Country"
												placeholder="Country"
												options={optionCountryCodes}
												onChange={handleCountry}
											/>
										</Form.Item>

										<Form.Item
											name="state"
											hasFeedback
											className="form-select-error"
											rules={[
												{
													required: true,
													message: "This field field is required.",
												},
											]}
										>
											<FloatSelect
												label={stateLabel}
												placeholder={stateLabel}
												options={optionState}
											/>
										</Form.Item>

										<Form.Item
											name="zip"
											hasFeedback
											className="w-100"
											rules={[
												{
													required: true,
													message: "This field is required.",
												},
												{
													pattern: optionZip,
													message: "Invalid Zip",
												},
											]}
										>
											<FloatInput label={zipLabel} placeholder={zipLabel} />
										</Form.Item>

										<Button
											type="primary"
											htmlType="submit"
											className="btn-main b-r-none"
											block
											size="large"
										>
											CONTINUE
										</Button>
									</Form>
								</Collapse.Panel>

								{formData[0].step === "done" ? (
									<Collapse.Panel
										header={
											<>
												<div className="title">Step 2</div>
												<div className="sub-title">
													Select Your Program Type
												</div>
											</>
										}
										key="2"
									>
										<Form layout="vertical" autoComplete="off">
											<Form.Item name="program_type" hasFeedback>
												<FloatSelectWithDangerouslySetInnerHTML
													label="Program Type"
													placeholder="Program Type"
													options={programTypes}
													onChange={(e) => {
														let val = programTypes.filter((x) => x.value === e);
														// console.log("val", val);
														setSelectedProgramType({
															...val[0],
															coupon_apply: 0,
															coupon: "",
															message: "",
														});
													}}
												/>
											</Form.Item>
										</Form>

										{selectedProgramType ? (
											<Form layout="vertical" autoComplete="off">
												<Form.Item
													name="coupon"
													rules={[
														{
															required: false,
															message: "This field is required.",
														},
													]}
													hasFeedback
													className="m-b-none"
												>
													<FloatInput
														label="Coupon"
														placeholder="Coupon"
														onChange={(e) => {
															setSelectedProgramType({
																...selectedProgramType,
																coupon_apply: 0,
																coupon: e,
																message: "",
															});
														}}
														addonAfter={
															<Button
																style={{
																	height: "46px",
																	marginTop: "-1px",
																}}
																onClick={(e) => handleApplyCoupon(e)}
															>
																APPLY
															</Button>
														}
													/>
												</Form.Item>
												{selectedProgramType && selectedProgramType.message ? (
													selectedProgramType.message === "Invalid Code" ? (
														<Typography.Text className="color-6">
															{selectedProgramType.message}
														</Typography.Text>
													) : (
														<Typography.Text className="color-11">
															{selectedProgramType.message}
														</Typography.Text>
													)
												) : null}
												<br />
												<Typography.Text>
													Total: ${selectedProgramType.amount}
												</Typography.Text>

												<Button
													type="primary"
													className="btn-main b-r-none m-t-sm"
													block
													size="large"
													onClick={handleProceedToCheckout}
												>
													PROCEED TO CHECKOUT
												</Button>
											</Form>
										) : null}
									</Collapse.Panel>
								) : null}

								{formData[1].step === "done" ? (
									<Collapse.Panel
										header={
											<>
												<div className="title">Step 3</div>
												<div className="sub-title">
													<div
														dangerouslySetInnerHTML={{
															__html:
																selectedProgramType && selectedProgramType.label
																	? selectedProgramType.label
																	: null,
														}}
													/>
												</div>
											</>
										}
										key="3"
									>
										<Typography.Title
											level={3}
											className="m-t-md font-weight-normal w-100"
										>
											Credit Card Information
										</Typography.Title>

										<Form
											layout="vertical"
											autoComplete="off"
											onFinish={onFinishCompletePurchase}
										>
											<Form.Item
												name="credit_card_name"
												hasFeedback
												className="w-100"
											>
												<FloatInput
													label="Name on Card"
													placeholder="Name on Card"
												/>
											</Form.Item>

											<Form.Item
												name="credit_card_number"
												hasFeedback
												className="w-100"
												rules={[
													{
														required: true,
														message: "This field is required.",
													},
												]}
											>
												<FloatInputMask
													label="Card Number"
													placeholder="Card Number"
													maskLabel="credit_card_number"
													// onChange={handleChangeCreditCardNumber}
													// value={creditCardNumber}
												/>
											</Form.Item>

											<Form.Item
												name="credit_expiry"
												hasFeedback
												className="w-100"
												rules={[
													{
														required: true,
														message: "This field is required.",
													},
												]}
											>
												<FloatInputMask
													label="Expiration"
													placeholder="Expiration"
													maskLabel="card_expiry"
													maskType="99/99"
												/>
											</Form.Item>

											<Form.Item
												name="credit_cvv"
												hasFeedback
												className="w-100"
												rules={[
													{
														required: true,
														message: "This field is required.",
													},
												]}
											>
												<FloatInputMask
													label="Security Code (CVV)"
													placeholder="Security Code (CVV)"
													maskLabel="cvv"
													maskType="999"
												/>
											</Form.Item>

											<Typography.Title
												level={3}
												className="font-weight-normal w-100"
											>
												Billing Address
											</Typography.Title>

											<Form.Item
												name="billing_street_address1"
												hasFeedback
												className="w-100"
												rules={[
													{
														required: true,
														message: "This field is required.",
													},
												]}
											>
												<FloatInput
													label="Street Address"
													placeholder="Street Address"
												/>
											</Form.Item>

											<Form.Item
												name="billing_street_address2"
												hasFeedback
												className="w-100"
											>
												<FloatInput
													label="Street Address 2"
													placeholder="Street Address 2"
												/>
											</Form.Item>

											<Form.Item
												name="billing_country"
												hasFeedback
												className="form-select-error w-100"
												rules={[
													{
														required: true,
														message: "This field is required.",
													},
												]}
											>
												<FloatSelect
													label="Country"
													placeholder="Country"
													options={optionCountryCodes}
													onChange={handleChangeBillingCountry}
												/>
											</Form.Item>

											<Form.Item
												name="billing_state"
												hasFeedback
												className="form-select-error w-100"
												rules={[
													{
														required: true,
														message: "This field is required.",
													},
												]}
											>
												<FloatSelect
													label={billingStateLabel}
													placeholder={billingStateLabel}
													options={optionBillingState}
												/>
											</Form.Item>

											<Form.Item
												name="billing_city"
												hasFeedback
												className="w-100"
												rules={[
													{
														required: true,
														message: "This field is required.",
													},
												]}
											>
												<FloatInput label="City" placeholder="City" />
											</Form.Item>

											<Form.Item
												name="billing_zip"
												hasFeedback
												className="w-100"
												rules={[
													{
														required: true,
														message: "This field is required.",
													},
													{
														pattern: optionBillingZip,
														message: "Invalid Zip",
													},
												]}
											>
												<FloatInput
													label={billingZipLabel}
													placeholder={billingZipLabel}
												/>
											</Form.Item>
											<br />
											<h1 style={{ fontWeight: "normal " }}>
												Privacy Policy & Terms and Conditions
											</h1>
											<div style={{ marginTop: -9 }} className="c-danger">
												<b>Please read / scroll to the end to continue.</b>
											</div>
											<div
												onScroll={handleScroll}
												className="scrollbar-2"
												style={{
													marginBottom: 10,
													marginTop: 10,
													height: 170,
													resize: "vertical",

													overflow: "auto",
													border: "1px solid #58585a",
												}}
												dangerouslySetInnerHTML={{
													__html:
														selectedProgramType && selectedProgramType.policy,
												}}
											></div>

											<Checkbox
												onChange={onChangeCheckbox}
												name="checkbox_2"
												className="checkbox_yes"
												disabled={checkboxYes}
											>
												Yes, I have read the Privacy Policy and Terms and
												Conditions
											</Checkbox>

											<ReCAPTCHA
												style={{ marginTop: 10 }}
												onChange={(token) =>
													setCaptchaToken({ ...captchaToken, token })
												}
												className="captcha-registration"
												// theme="dark"
												render="explicit"
												// render="explicit"
												sitekey={`${process.env.REACT_APP_RECAPTCHA_API_KEY}`}
												// onloadCallback={() => {}}
											/>
											{captchaToken.error && (
												<span
													style={{
														color: "#dc3545",
													}}
												>
													Please Verify Captcha
												</span>
											)}

											<Button
												type="primary"
												htmlType="submit"
												loading={isLoadingRegister}
												className="btn-main m-t-sm btn-complete-purchase"
												block
												size="large"
												disabled={
													checkboxYes ? true : checkboxYesStatus ? false : true
												}
											>
												COMPLETE PURCHASE
											</Button>
											{completePurchaseErr.message && (
												<Alert
													className="m-t-sm"
													type={completePurchaseErr.type}
													message={completePurchaseErr.message}
												/>
											)}
										</Form>
									</Collapse.Panel>
								) : null}
							</Collapse>

							<div>
								<Typography.Text>
									This page is protected by reCAPTCHA, and subject to the Google{" "}
									<Typography.Link
										href="https://policies.google.com/privacy?hl=en"
										className="color-1"
										target="new"
									>
										Privacy Policy
									</Typography.Link>{" "}
									and{" "}
									<Typography.Link
										href="https://policies.google.com/terms?hl=en"
										className="color-1"
										target="new"
									>
										Terms of Services.
									</Typography.Link>
								</Typography.Text>
							</div>
						</Card>
					</Col>
				</Row>
			</Layout.Content>
			<Layout.Footer className="text-center">
				<Typography.Text>
					© Copyright {moment().format("YYYY")} {description}. All Rights
					Reserved..
				</Typography.Text>
			</Layout.Footer>
		</Layout>
	);
}
