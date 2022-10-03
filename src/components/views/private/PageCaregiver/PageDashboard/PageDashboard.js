import React, { useState } from "react";
import { Card, Col, Collapse, Row } from "antd";

import Highcharts from "highcharts";
import { Link } from "react-router-dom";
import { GET } from "../../../../providers/useAxiosQuery";
import moment from "moment";
// import $ from "jquery";
// import { userData } from "../../../../providers/companyInfo";
// require("highcharts/modules/exporting")(Highcharts);
require("highcharts/modules/boost")(Highcharts);

export default function PageDashboard() {
	const [dataUserPayment, setDataUserPayment] = useState([]);

	GET(`api/v1/user_payment`, "user_payment_dashboard_list", (res) => {
		// console.log("user_payment_dashboard_list", res.data);
		if (res.data) {
			setDataUserPayment(res.data);
		}
	});

	// const [hasCollapse, setHasCollapse] = useState(false);
	// useEffect(() => {
	// 	$("#btn_sidemenu_collapse_unfold").on("click", function () {
	// 		setHasCollapse(false);
	// 		// console.log("btn_sidemenu_collapse_unfold");
	// 	});
	// 	$("#btn_sidemenu_collapse_fold").on("click", function () {
	// 		setHasCollapse(true);
	// 		// console.log("btn_sidemenu_collapse_fold");
	// 	});

	// 	return () => {};
	// }, []);

	return (
		<Card className="page-dashboard-caregiver" id="PageDashboard">
			<Row gutter={[12, 12]}>
				<Col xs={24} sm={24} md={24} lg={24} xl={7}>
					<Collapse
						className="main-1-collapse border-none"
						expandIcon={({ isActive }) =>
							isActive ? (
								<span
									className="ant-menu-submenu-arrow"
									style={{ color: "#FFF", transform: "rotate(270deg)" }}
								></span>
							) : (
								<span
									className="ant-menu-submenu-arrow"
									style={{ color: "#FFF", transform: "rotate(90deg)" }}
								></span>
							)
						}
						defaultActiveKey={["1"]}
						expandIconPosition="start"
					>
						<Collapse.Panel
							header="MY INVOICES"
							key="1"
							className="accordion bg-darkgray-form m-b-md border collapse-recent-invoices"
						>
							<table className="table table-striped m-b-n">
								<thead>
									<tr>
										<th>Invoice</th>
										<th>Date</th>
										<th>Amount</th>
									</tr>
								</thead>
								<tbody>
									{dataUserPayment.map((item, index) => {
										return (
											<tr key={index}>
												<td>
													<Link
														className="color-6"
														to={{
															pathname: "/profile/account/payment-and-invoices",
															state: item,
														}}
													>
														#{item.invoice_id}
													</Link>
												</td>
												<td>
													{moment(item.date_paid).format("MMMM DD, YYYY")}
												</td>
												<td>${item.amount}</td>
											</tr>
										);
									})}
								</tbody>
							</table>
						</Collapse.Panel>
					</Collapse>
				</Col>
			</Row>
		</Card>
	);
}
