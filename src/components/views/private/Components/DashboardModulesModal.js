import { useEffect, useRef, useState } from "react";
import { Link, useHistory } from "react-router-dom";
import { Button, Carousel, Col, Modal, Row, Table, Typography } from "antd";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
	faArrowLeft,
	faArrowRight,
	faForward,
	faDial,
	faCheckCircle,
	faTimes,
} from "@fortawesome/pro-solid-svg-icons";

export default function DashboardModulesModal(props) {
	const { toggleModalModule, setToggleModalModule } = props;
	// console.log("props", props);
	const history = useHistory();
	const refCarousel = useRef();
	let maxContentCarousel =
		toggleModalModule &&
		toggleModalModule.data &&
		toggleModalModule.data.filter(
			(itemFiltered) => itemFiltered.status !== "Up Next"
		).length;
	const [carouselCurrent, setCarouselCurrent] = useState(0);

	useEffect(() => {
		// console.log("toggleModalModule", toggleModalModule);
		if (toggleModalModule.data) {
			setCarouselCurrent(toggleModalModule.index);
			refCarousel.current.goTo(toggleModalModule.index, false);
		}

		return () => {};
	}, [toggleModalModule]);

	const onAfterChangeCarousel = (currentSlide) => {
		// console.log(currentSlide);
		setCarouselCurrent(currentSlide);
	};

	const handleClickPrev = () => {
		if (carouselCurrent !== 0) {
			refCarousel.current.goTo(carouselCurrent - 1, false);
		}
	};
	const handleClickNext = () => {
		// console.log("maxContentCarousel", maxContentCarousel);
		if (carouselCurrent !== maxContentCarousel - 1) {
			refCarousel.current.goTo(carouselCurrent + 1, false);
		}
	};

	return (
		<Modal
			closeIcon={<FontAwesomeIcon icon={faTimes} />}
			visible={toggleModalModule.show}
			footer={null}
			onCancel={() =>
				setToggleModalModule({ show: false, data: null, index: 0 })
			}
			style={{ padding: "20px 10px" }}
			className="dashboard-caregiver-modal-module"
			wrapClassName="dashboard-caregiver-modal-module-wrap"
		>
			<div className="carousel-control">
				<div className="prev">
					<Button
						type="link"
						className={carouselCurrent === 0 ? "disabled" : ""}
						onClick={handleClickPrev}
					>
						<FontAwesomeIcon icon={faArrowLeft} className="m-r-xs" />{" "}
						<span>PREVIOUS</span>
					</Button>
				</div>
				<div className="next">
					<Button
						type="link"
						className={
							toggleModalModule.data && toggleModalModule.data.length > 1
								? carouselCurrent === maxContentCarousel - 1
									? "disabled"
									: ""
								: "disabled"
						}
						onClick={handleClickNext}
					>
						<span>NEXT</span>{" "}
						<FontAwesomeIcon icon={faArrowRight} className="m-l-xs" />
					</Button>
				</div>
			</div>
			<Carousel
				ref={refCarousel}
				dots={false}
				afterChange={onAfterChangeCarousel}
			>
				{toggleModalModule &&
					toggleModalModule.data &&
					toggleModalModule.data.map((item, index) => {
						return (
							<div key={index}>
								<Row>
									<Col xs={24} sm={24} md={24}>
										<div className="text-center">
											<Typography.Title
												level={4}
												className="line-height-1 color-6 m-n"
											>
												{item.module_number}
											</Typography.Title>
											<Typography.Title
												level={5}
												className="color-1 line-height-1 m-n"
											>
												{item.module_name}
											</Typography.Title>
											<Typography.Text>
												Select any lesson to re-review or to continue
											</Typography.Text>
										</div>
									</Col>

									<Col xs={24} sm={24} md={24} className="m-t-sm">
										<Table
											className="ant-table-default ant-table-striped"
											dataSource={item.lessons && item.lessons}
											rowKey={(record) => record.id}
											pagination={false}
											bordered={false}
											// rowSelection={{
											//   type: selectionType,
											//   ...rowSelection,
											// }}
											scroll={{ x: "max-content" }}
										>
											<Table.Column
												title="Lesson #"
												key="lesson_number"
												dataIndex="lesson_number"
												render={(_, record) => {
													let link = "";
													if (record.status === "Completed") {
														link = (
															<Link
																to={{
																	pathname: "/training-modules/view",
																	state: {
																		parentId: item.id,
																		childId: record.id,
																	},
																}}
																className="color-5"
															>
																{record.lesson_number}
															</Link>
														);
													} else if (record.status === "In Progress") {
														link = (
															<Link
																to={{
																	pathname: "/training-modules/view",
																	state: {
																		parentId: item.id,
																		childId: record.id,
																	},
																}}
																className="color-1"
															>
																{record.lesson_number}
															</Link>
														);
													} else if (record.status === "Up Next") {
														link = (
															<span className="color-7">
																{record.lesson_number}
															</span>
														);
													}
													return link;
												}}
											/>
											<Table.Column
												title="Title"
												key="lesson_name"
												dataIndex="lesson_name"
												width={400}
												render={(_, record) => {
													let link = "";
													if (record.status === "Completed") {
														link = (
															<Link
																to={{
																	pathname: "/training-modules/view",
																	state: {
																		parentId: item.id,
																		childId: record.id,
																	},
																}}
																className="color-5"
															>
																{record.lesson_name}
															</Link>
														);
													} else if (record.status === "In Progress") {
														link = (
															<Link
																to={{
																	pathname: "/training-modules/view",
																	state: {
																		parentId: item.id,
																		childId: record.id,
																	},
																}}
																className="color-1"
															>
																{record.lesson_name}
															</Link>
														);
													} else if (record.status === "Up Next") {
														link = (
															<span className="color-7">
																{record.lesson_name}
															</span>
														);
													}
													return link;
												}}
											/>
											<Table.Column
												title="Status"
												key="action"
												align="center"
												render={(_, record) => {
													let color = "";
													let icon = faCheckCircle;
													if (record.status === "Completed") {
														color = "color-5";
														icon = faCheckCircle;
													} else if (record.status === "In Progress") {
														color = "color-1";
														icon = faDial;
													} else if (record.status === "Up Next") {
														color = "color-7";
														icon = faForward;
													}
													return (
														<Button
															type="link"
															className={color}
															onClick={() => {
																if (record.status !== "Up Next") {
																	history.push({
																		pathname: "/training-modules/view",
																		state: {
																			parentId: item.id,
																			childId: record.id,
																		},
																	});
																}
															}}
														>
															<FontAwesomeIcon icon={icon} />
														</Button>
													);
												}}
											/>
										</Table>
									</Col>
								</Row>
							</div>
						);
					})}
			</Carousel>
		</Modal>
	);
}
