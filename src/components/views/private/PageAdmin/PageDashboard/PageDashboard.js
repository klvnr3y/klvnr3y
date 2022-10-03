import React, { useEffect, useState } from "react";
import { useHistory } from "react-router-dom";
import { Button, Card, Col, Row, Typography } from "antd";

import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
	faUsers,
	faAnalytics,
	faChartPie,
	faUsdCircle,
} from "@fortawesome/pro-light-svg-icons";

import $ from "jquery";

import Highcharts from "highcharts";
// require("highcharts/modules/exporting")(Highcharts);
require("highcharts/modules/boost")(Highcharts);

export default function PageDashboard() {
	const history = useHistory();
	const [hasCollapse, setHasCollapse] = useState(
		$(".private-layout > .ant-layout").hasClass("ant-layout-has-collapse")
			? true
			: false
	);

	useEffect(() => {
		$("#btn_sidemenu_collapse_unfold").on("click", function () {
			setHasCollapse(false);
			// console.log("btn_sidemenu_collapse_unfold");
		});
		$("#btn_sidemenu_collapse_fold").on("click", function () {
			setHasCollapse(true);
			// console.log("btn_sidemenu_collapse_fold");
		});

		return () => {};
	}, []);

	return (
		<Card id="PageDashboard">
			<Row gutter={[12, 12]}>
				<Col xs={24} sm={24} md={24} lg={24} xl={16}>
					<Card
						// title="QUICK LINKS"
						className="card-transparent-head border-bottom-none no-side-border p-none card-quick-links"
						bodyStyle={{ padding: "0px" }}
					>
						<Row gutter={[12, 12]} className="ant-row-quick-link">
							<Col xs={12} sm={12} md={12} lg={hasCollapse ? 6 : 12} xl={6}>
								<Button
									type="link"
									className="ant-btn-quick-link"
									onClick={() => history.push("/subscribers/current")}
								>
									<FontAwesomeIcon icon={faUsers} />
									<Typography.Text>Current Subscribers</Typography.Text>
								</Button>
							</Col>
							<Col xs={12} sm={12} md={12} lg={hasCollapse ? 6 : 12} xl={6}>
								<Button
									type="link"
									className="ant-btn-quick-link"
									onClick={() => history.push("/revenue")}
								>
									<FontAwesomeIcon icon={faUsdCircle} />
									<Typography.Text>Revenue</Typography.Text>
								</Button>
							</Col>
							<Col xs={12} sm={12} md={12} lg={hasCollapse ? 6 : 12} xl={6}>
								<Button
									type="link"
									className="ant-btn-quick-link"
									onClick={() => history.push("/training-modules/edit")}
								>
									<FontAwesomeIcon icon={faAnalytics} />
									<Typography.Text>Edit Module</Typography.Text>
								</Button>
							</Col>
							<Col xs={12} sm={12} md={12} lg={hasCollapse ? 6 : 12} xl={6}>
								<Button
									type="link"
									className="ant-btn-quick-link"
									onClick={() => history.push("/stats")}
								>
									<FontAwesomeIcon icon={faChartPie} />
									<Typography.Text>Stats</Typography.Text>
								</Button>
							</Col>
						</Row>
					</Card>
				</Col>
			</Row>
		</Card>
	);
}
