
CREATE TABLE [dbo].[queue](
	[queue_id] [int] IDENTITY(1,1) NOT NULL,
	[queue_name] [varchar](100) NOT NULL,
	[timeout] [int] NOT NULL,
 CONSTRAINT [PK_queue] PRIMARY KEY CLUSTERED 
(
	[queue_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[queue] ADD  DEFAULT ((30)) FOR [timeout]
GO


CREATE TABLE [dbo].[message](
	[message_id] [bigint] IDENTITY(1,1) NOT NULL,
	[queue_id] [int] NOT NULL,
	[handle] [char](32) NULL,
	[body] [varchar](max) NOT NULL,
	[md5] [char](32) NOT NULL,
	[timeout] [decimal](14, 4) NULL,
	[created] [int] NOT NULL,
 CONSTRAINT [PK_message] PRIMARY KEY CLUSTERED 
(
	[message_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
) ON [PRIMARY]

GO

ALTER TABLE [dbo].[message]  WITH CHECK ADD  CONSTRAINT [fk_message_queue_id] FOREIGN KEY([queue_id])
REFERENCES [dbo].[queue] ([queue_id])
GO

ALTER TABLE [dbo].[message] CHECK CONSTRAINT [fk_message_queue_id]
GO





