--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5 (Postgres.app)
-- Dumped by pg_dump version 17.5 (Postgres.app)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: students; Type: TABLE; Schema: public; Owner: student_site_admin
--

CREATE TABLE public.students (
    student_id integer NOT NULL,
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    email character varying(100) NOT NULL,
    assigned_directory_slug character varying(100) NOT NULL,
    current_storage_bytes bigint DEFAULT 0,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.students OWNER TO student_site_admin;

--
-- Name: students_student_id_seq; Type: SEQUENCE; Schema: public; Owner: student_site_admin
--

CREATE SEQUENCE public.students_student_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.students_student_id_seq OWNER TO student_site_admin;

--
-- Name: students_student_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: student_site_admin
--

ALTER SEQUENCE public.students_student_id_seq OWNED BY public.students.student_id;


--
-- Name: students student_id; Type: DEFAULT; Schema: public; Owner: student_site_admin
--

ALTER TABLE ONLY public.students ALTER COLUMN student_id SET DEFAULT nextval('public.students_student_id_seq'::regclass);


--
-- Data for Name: students; Type: TABLE DATA; Schema: public; Owner: student_site_admin
--

COPY public.students (student_id, username, password_hash, email, assigned_directory_slug, current_storage_bytes, created_at) FROM stdin;
2	user2	$2y$10$3O.ldf0OrJ/dX15tgftyU.LD/aFWwR7hHp5K6oaxbx9hybqzFwloq	user2@gmail.com	user2	9293	2025-05-16 13:03:11.33054+05:30
1	user1	$2y$10$sIMCvdGKD4CagWcrkuEiPe0UFI9z7NYKsUL3qd/jnp7jej9dsKaQW	user1@gmail.com	user1	1493019	2025-05-15 20:56:59.87741+05:30
\.


--
-- Name: students_student_id_seq; Type: SEQUENCE SET; Schema: public; Owner: student_site_admin
--

SELECT pg_catalog.setval('public.students_student_id_seq', 2, true);


--
-- Name: students students_assigned_directory_slug_key; Type: CONSTRAINT; Schema: public; Owner: student_site_admin
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_assigned_directory_slug_key UNIQUE (assigned_directory_slug);


--
-- Name: students students_email_key; Type: CONSTRAINT; Schema: public; Owner: student_site_admin
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_email_key UNIQUE (email);


--
-- Name: students students_pkey; Type: CONSTRAINT; Schema: public; Owner: student_site_admin
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_pkey PRIMARY KEY (student_id);


--
-- Name: students students_username_key; Type: CONSTRAINT; Schema: public; Owner: student_site_admin
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_username_key UNIQUE (username);


--
-- Name: idx_students_directory_slug; Type: INDEX; Schema: public; Owner: student_site_admin
--

CREATE INDEX idx_students_directory_slug ON public.students USING btree (assigned_directory_slug);


--
-- PostgreSQL database dump complete
--

