import React from "react";
import { Link } from "react-router-dom";
import { Menu, Typography } from "antd";
import {
	faUsers,
	faLightbulbOn,
	faAnalytics,
} from "@fortawesome/pro-light-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

export const menuLeft = (
	<>
		<div className="ant-menu-left-icon">
			<Link to="/support/faqs">
				<span className="anticon">
					<FontAwesomeIcon icon={faLightbulbOn} />
				</span>
				<Typography.Text>FAQ's</Typography.Text>
			</Link>
		</div>
	</>
);

export const dropDownMenuLeft = () => {
	const items = [
		{
			key: "/support/faq",
			icon: <FontAwesomeIcon icon={faLightbulbOn} />,
			label: <Link to="/support/faqs">FAQ's</Link>,
		},
	];

	return <Menu items={items} />;
};
