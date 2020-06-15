PGDMP         (                x            chan    12.3    12.3 +    7           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                      false            8           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                      false            9           0    0 
   SEARCHPATH 
   SEARCHPATH     8   SELECT pg_catalog.set_config('search_path', '', false);
                      false            :           1262    16394    chan    DATABASE     �   CREATE DATABASE chan WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'Russian_Russia.1251' LC_CTYPE = 'Russian_Russia.1251';
    DROP DATABASE chan;
                systemdaemon    false            ;           0    0    DATABASE chan    COMMENT     '   COMMENT ON DATABASE chan IS 'main db';
                   systemdaemon    false    2874            �            1259    16472    anonym_id_seq    SEQUENCE     ~   CREATE SEQUENCE public.anonym_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    MAXVALUE 2147483647
    CACHE 1;
 $   DROP SEQUENCE public.anonym_id_seq;
       public          postgres    false            �            1259    16457    anonym    TABLE     �   CREATE TABLE public.anonym (
    id bigint DEFAULT nextval('public.anonym_id_seq'::regclass) NOT NULL,
    hash character varying(256) NOT NULL
);
    DROP TABLE public.anonym;
       public         heap    postgres    false    210            <           0    0    TABLE anonym    COMMENT     2   COMMENT ON TABLE public.anonym IS 'same as user';
          public          postgres    false    209            �            1259    16397    boards    TABLE     �  CREATE TABLE public.boards (
    id integer NOT NULL,
    description character varying(128),
    name character varying(32) NOT NULL,
    uri character varying(8) NOT NULL,
    threads_max smallint DEFAULT 15 NOT NULL,
    anon_name character varying(16) DEFAULT 'Аноним'::character varying NOT NULL,
    banner_uri character varying(32) DEFAULT 'no-banner'::character varying NOT NULL
);
    DROP TABLE public.boards;
       public         heap    systemdaemon    false            =           0    0    TABLE boards    COMMENT     >   COMMENT ON TABLE public.boards IS 'table of existing boards';
          public          systemdaemon    false    203            �            1259    16395    board_id_seq    SEQUENCE     �   CREATE SEQUENCE public.board_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 #   DROP SEQUENCE public.board_id_seq;
       public          systemdaemon    false    203            >           0    0    board_id_seq    SEQUENCE OWNED BY     >   ALTER SEQUENCE public.board_id_seq OWNED BY public.boards.id;
          public          systemdaemon    false    202            �            1259    16446    media    TABLE     �   CREATE TABLE public.media (
    id bigint NOT NULL,
    type character varying(8) NOT NULL,
    uri character varying(64) NOT NULL,
    post_id bigint NOT NULL
);
    DROP TABLE public.media;
       public         heap    systemdaemon    false            ?           0    0    TABLE media    COMMENT     L   COMMENT ON TABLE public.media IS 'pictures/video/archives and other files';
          public          systemdaemon    false    208            �            1259    16444    media_id_seq    SEQUENCE     u   CREATE SEQUENCE public.media_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 #   DROP SEQUENCE public.media_id_seq;
       public          systemdaemon    false    208            @           0    0    media_id_seq    SEQUENCE OWNED BY     =   ALTER SEQUENCE public.media_id_seq OWNED BY public.media.id;
          public          systemdaemon    false    207            �            1259    16424    posts    TABLE     �   CREATE TABLE public.posts (
    id bigint NOT NULL,
    op boolean DEFAULT false,
    sage boolean DEFAULT false,
    date date DEFAULT now() NOT NULL,
    text character varying(8192) NOT NULL,
    anon_id bigint NOT NULL,
    thread_id bigint
);
    DROP TABLE public.posts;
       public         heap    systemdaemon    false            A           0    0    TABLE posts    COMMENT     /   COMMENT ON TABLE public.posts IS 'anon posts';
          public          systemdaemon    false    206            �            1259    16422    posts_id_seq    SEQUENCE     u   CREATE SEQUENCE public.posts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 #   DROP SEQUENCE public.posts_id_seq;
       public          systemdaemon    false    206            B           0    0    posts_id_seq    SEQUENCE OWNED BY     =   ALTER SEQUENCE public.posts_id_seq OWNED BY public.posts.id;
          public          systemdaemon    false    205            �            1259    16405    threads    TABLE     q   CREATE TABLE public.threads (
    id bigint NOT NULL,
    board_id bigint NOT NULL,
    op_id bigint NOT NULL
);
    DROP TABLE public.threads;
       public         heap    systemdaemon    false            C           0    0    TABLE threads    COMMENT     A   COMMENT ON TABLE public.threads IS 'table of threads of boards';
          public          systemdaemon    false    204            �
           2604    16400 	   boards id    DEFAULT     e   ALTER TABLE ONLY public.boards ALTER COLUMN id SET DEFAULT nextval('public.board_id_seq'::regclass);
 8   ALTER TABLE public.boards ALTER COLUMN id DROP DEFAULT;
       public          systemdaemon    false    202    203    203            �
           2604    16449    media id    DEFAULT     d   ALTER TABLE ONLY public.media ALTER COLUMN id SET DEFAULT nextval('public.media_id_seq'::regclass);
 7   ALTER TABLE public.media ALTER COLUMN id DROP DEFAULT;
       public          systemdaemon    false    207    208    208            �
           2604    16427    posts id    DEFAULT     d   ALTER TABLE ONLY public.posts ALTER COLUMN id SET DEFAULT nextval('public.posts_id_seq'::regclass);
 7   ALTER TABLE public.posts ALTER COLUMN id DROP DEFAULT;
       public          systemdaemon    false    206    205    206            3          0    16457    anonym 
   TABLE DATA           *   COPY public.anonym (id, hash) FROM stdin;
    public          postgres    false    209   �,       -          0    16397    boards 
   TABLE DATA           `   COPY public.boards (id, description, name, uri, threads_max, anon_name, banner_uri) FROM stdin;
    public          systemdaemon    false    203   �,       2          0    16446    media 
   TABLE DATA           7   COPY public.media (id, type, uri, post_id) FROM stdin;
    public          systemdaemon    false    208   �.       0          0    16424    posts 
   TABLE DATA           M   COPY public.posts (id, op, sage, date, text, anon_id, thread_id) FROM stdin;
    public          systemdaemon    false    206   �.       .          0    16405    threads 
   TABLE DATA           6   COPY public.threads (id, board_id, op_id) FROM stdin;
    public          systemdaemon    false    204   21       D           0    0    anonym_id_seq    SEQUENCE SET     ;   SELECT pg_catalog.setval('public.anonym_id_seq', 1, true);
          public          postgres    false    210            E           0    0    board_id_seq    SEQUENCE SET     :   SELECT pg_catalog.setval('public.board_id_seq', 9, true);
          public          systemdaemon    false    202            F           0    0    media_id_seq    SEQUENCE SET     :   SELECT pg_catalog.setval('public.media_id_seq', 1, true);
          public          systemdaemon    false    207            G           0    0    posts_id_seq    SEQUENCE SET     :   SELECT pg_catalog.setval('public.posts_id_seq', 8, true);
          public          systemdaemon    false    205            �
           2606    16461    anonym anonym_pkey 
   CONSTRAINT     P   ALTER TABLE ONLY public.anonym
    ADD CONSTRAINT anonym_pkey PRIMARY KEY (id);
 <   ALTER TABLE ONLY public.anonym DROP CONSTRAINT anonym_pkey;
       public            postgres    false    209            �
           2606    16402    boards board_pkey 
   CONSTRAINT     O   ALTER TABLE ONLY public.boards
    ADD CONSTRAINT board_pkey PRIMARY KEY (id);
 ;   ALTER TABLE ONLY public.boards DROP CONSTRAINT board_pkey;
       public            systemdaemon    false    203            �
           2606    16451    media media_pkey 
   CONSTRAINT     N   ALTER TABLE ONLY public.media
    ADD CONSTRAINT media_pkey PRIMARY KEY (id);
 :   ALTER TABLE ONLY public.media DROP CONSTRAINT media_pkey;
       public            systemdaemon    false    208            �
           2606    16435    posts posts_pkey 
   CONSTRAINT     N   ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (id);
 :   ALTER TABLE ONLY public.posts DROP CONSTRAINT posts_pkey;
       public            systemdaemon    false    206            �
           2606    16410    threads thread_pkey 
   CONSTRAINT     Q   ALTER TABLE ONLY public.threads
    ADD CONSTRAINT thread_pkey PRIMARY KEY (id);
 =   ALTER TABLE ONLY public.threads DROP CONSTRAINT thread_pkey;
       public            systemdaemon    false    204            �
           2606    16417    threads board_key    FK CONSTRAINT     |   ALTER TABLE ONLY public.threads
    ADD CONSTRAINT board_key FOREIGN KEY (board_id) REFERENCES public.boards(id) NOT VALID;
 ;   ALTER TABLE ONLY public.threads DROP CONSTRAINT board_key;
       public          systemdaemon    false    2721    203    204            �
           2606    16452    media post_key    FK CONSTRAINT     m   ALTER TABLE ONLY public.media
    ADD CONSTRAINT post_key FOREIGN KEY (post_id) REFERENCES public.posts(id);
 8   ALTER TABLE ONLY public.media DROP CONSTRAINT post_key;
       public          systemdaemon    false    208    206    2725            �
           2606    16462    posts post_key    FK CONSTRAINT     x   ALTER TABLE ONLY public.posts
    ADD CONSTRAINT post_key FOREIGN KEY (anon_id) REFERENCES public.anonym(id) NOT VALID;
 8   ALTER TABLE ONLY public.posts DROP CONSTRAINT post_key;
       public          systemdaemon    false    206    209    2729            �
           2606    16475    posts thread_id    FK CONSTRAINT     |   ALTER TABLE ONLY public.posts
    ADD CONSTRAINT thread_id FOREIGN KEY (thread_id) REFERENCES public.threads(id) NOT VALID;
 9   ALTER TABLE ONLY public.posts DROP CONSTRAINT thread_id;
       public          systemdaemon    false    206    204    2723            3      x�3�44261����� ��      -   �  x�]RIK�@>����w=z���r�u"mD{k���~E�Ѧcm���&�F	Yf^޷�'��ip¡∿̅2�6<cS�.�s����Pxai�̑2���eb�9�O���l���<n5���A�m������[ɍ_c�_|�̦	Θ?�C��!	�P���:�����j���B{,`ܳ�[x�������!��e)^PO������F{�R���ʁZ���^�U~�+95r�)ʍ �����u׃㮕�@�~�͉d�Pl�Dm��(V��X����^Z�b��!������5�����扟l���`��~T$40
F����9���vU��*�r՞_)��ծ�w�u�V�x�v��i+'�Ȁ�
��#.�!�1_�b[&� ��?�7+j���kK��Q�+��;8�����ۜIZH���z�;��(��!d��r��t�K�����r��w��      2      x�3�,�L.)-J���O��4����� N��      0   d  x�eTKnA\{N��;�mB@lr�l[֜���1�؉l��H�h���\�Q���`Y�8������U��x�O���t�N��� s)�z)������������J��������	_
TN��T8X�'R,�#��x��6��p:сC:-���[�4�[X�F�lc�G@��ΰ��v���((�ơ�H�����k�;c��Z��~�8_���Z��aK�$��_h��B���]W|���|�)�A�L�l������ڑULڍn������O���y��Iƥ�i|=����� $��0���R���sN��������H��? ��{Yk+������+�o6�q
�CM��E	}1H����`dfs�k�c%��+���N��#�nL4��ײwc,1�,������q|q�Y�LZ4��c1��9��A,Z��ʒ|e�L}�R�6��Ǌy��@�&1[��������>hv{�ffy/cA��{y$�Κq��b�cemE�
W�����z�ʽ|�O�mO�Sp�s'?L
��m���z�-�S����#V1{��E�۲��8$D����-���Rg��_�=���ѱ7�/긾m'I�j��      .      x�3�4�4�2�1z\\\ 
     