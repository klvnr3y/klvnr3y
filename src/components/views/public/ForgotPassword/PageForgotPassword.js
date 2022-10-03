import React, { useState, useEffect } from "react";
import {
	Layout,
	Card,
	Form,
	Button,
	Row,
	Col,
	Image,
	Typography,
	Alert,
} from "antd";
import { useHistory } from "react-router-dom";
import moment from "moment";
import { apiUrl, logo, description } from "../../../providers/companyInfo";
import { POST } from "../../../providers/useAxiosQuery";
import FloatInputPassword from "../../../providers/FloatInputPassword";
import axios from "axios";

export default function PageForgotPassword({ match }) {
	let history = useHistory();
	let user_id = match.params.id;
	let token = match.params.token;
	let newApiUrl = apiUrl + "api/v1/";
	let url = `newpassword/auth`;
	const [form] = Form.useForm();

	useEffect(() => {
		axios
			.post(
				`${newApiUrl}${url}`,
				{},
				{
					headers: {
						Authorization: "Bearer " + token,
					},
				}
			)
			.then((res) => {
				console.log("success");
			})
			.catch((err) => {
				if (err.response.status === 401) {
					history.push("/error-500");
				}
			});
	}, [newApiUrl, url, token, history]);

	const [errorMessageLogin, setErrorMessageLogin] = useState({
		type: "success",
		message: "",
	});

	const { mutate: mutateChangePass, isLoading: isLoadingChangePass } = POST(
		"api/v1/change_password",
		"change_password"
	);

	const onFinishLogin = (values) => {
		let data = {
			...values,
			id: user_id,
		};
		mutateChangePass(data, {
			onSuccess: (res) => {
				if (res.success) {
					setErrorMessageLogin({
						type: "success",
						message: "Successfully updated",
					});
					form.resetFields();
				}
			},
			onError: (err) => {
				setErrorMessageLogin({
					type: "error",
					message: "Error",
				});
			},
		});
	};

	return (
		<Layout className="public-layout login-layout">
			<Layout.Content className="p-t-xl p-b-xl">
				<Row>
					<Col span={24}>
						<Image
							className="zoom-in-out-box"
							onClick={() => history.push("/")}
							src={logo}
							preview={false}
						/>

						<Card>
							<Form
								layout="vertical"
								name="new-password-form"
								className="new-password-form"
								onFinish={onFinishLogin}
								form={form}
								autoComplete="off"
							>
								<Typography.Title
									level={3}
									className="text-center text-create-user-account"
								>
									Create a New Password
									<h6>
										Your password must be at least 8 characters long and contain
										at least one number and one character.
									</h6>
								</Typography.Title>
								<Form.Item
									name="new_password"
									rules={[
										{
											required: true,
											message: "This field field is required.",
										},
									]}
									hasFeedback
									className="m-b-sm"
								>
									<FloatInputPassword label="Password" placeholder="Password" />
								</Form.Item>
								<Form.Item
									name="new_password_confirm"
									rules={[
										{
											required: true,
											message: "This field field is required.",
										},
										({ getFieldValue }) => ({
											validator(_, value) {
												if (!value || getFieldValue("new_password") === value) {
													return Promise.resolve();
												}
												return Promise.reject(
													new Error(
														"The two passwords that you entered do not match!"
													)
												);
											},
										}),
									]}
									hasFeedback
									className="m-b-sm"
								>
									<FloatInputPassword
										label="Confirm Password"
										placeholder="Confirm Password"
									/>
								</Form.Item>

								<Button
									type="primary"
									htmlType="submit"
									loading={isLoadingChangePass}
									className="btn-primary-default m-t-sm"
									block
									size="large"
								>
									Submit
								</Button>

								{errorMessageLogin.message && (
									<Alert
										className="m-t-sm"
										type={errorMessageLogin.type}
										message={errorMessageLogin.message}
									/>
								)}
							</Form>
						</Card>

						<footer>
							© Copyright {moment().format("YYYY")} {description}. All Rights
							Reserved.
						</footer>
					</Col>
				</Row>
			</Layout.Content>
		</Layout>
	);
}
