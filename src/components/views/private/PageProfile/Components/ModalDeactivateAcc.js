import { Button, message, Modal, Typography } from "antd";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTimes } from "@fortawesome/pro-solid-svg-icons";
import { POST } from "../../../../providers/useAxiosQuery";
import { userData } from "../../../../providers/companyInfo";

export default function ModalDeactivateAcc(props) {
	const { toggleModalDeactivateAcc, setToggleModalDeactivateAcc } = props;

	const { mutate: mutateDeactivate, isLoading: isLoadingDeactivate } = POST(
		"api/v1/user_deactive",
		"user_deactivate_acc"
	);

	const handleClickCancel = () => {
		message.info("Cancel");
		setToggleModalDeactivateAcc({
			...toggleModalDeactivateAcc,
			show: false,
		});
	};

	const handleClickYes = () => {
		mutateDeactivate(
			{ id: userData().id },
			{
				onSuccess: (res) => {
					if (res.success) {
						message.info("Successfully Deactivate Account");
						localStorage.clear();
						window.location.replace("/");
					} else {
						message.error("Error deactivating account, please try again later");
					}
				},
				onError: (err) => {
					message.error(err.response.data.message);
				},
			}
		);
	};

	return (
		<Modal
			title={toggleModalDeactivateAcc.title}
			visible={toggleModalDeactivateAcc.show}
			closeIcon={<FontAwesomeIcon icon={faTimes} />}
			footer={
				<>
					<Button
						size="large"
						className="btn-main-invert-outline-active"
						onClick={handleClickCancel}
					>
						NO
					</Button>
					<Button
						size="large"
						className="btn-main-invert-outline"
						onClick={handleClickYes}
						loading={isLoadingDeactivate}
					>
						YES, CANCEL MY ACCOUNT
					</Button>
				</>
			}
			onCancel={() =>
				setToggleModalDeactivateAcc({
					...toggleModalDeactivateAcc,
					show: false,
				})
			}
			className="modal-primary-default modal-account-deactivate"
		>
			<Typography.Title level={3} className="color-1">
				Do you want to cancel your account?
			</Typography.Title>
			<Typography.Text strong>
				By deactivating your account you will no longer have access to your
				account information and all historical data.
			</Typography.Text>
		</Modal>
	);
}
